<?php

namespace MintyPHP\Template\Tests;

use MintyPHP\Template\Template;
use PHPUnit\Framework\TestCase;

class RenderingTest extends TestCase
{
    private static Template $template;

    public static function setUpBeforeClass(): void
    {
        self::$template = new Template();
    }

    public function testRenderWithCustomFunction(): void
    {
        $template = new Template(null, ['capitalize' => 'ucfirst']);
        $result = $template->render('hello {{ name|capitalize }}', ['name' => 'world']);
        $this->assertEquals("hello World", $result);
    }

    public function testRenderWithHtmlEscaping(): void
    {
        $this->assertEquals("<br>hello &lt;br&gt;world", self::$template->render('<br>hello {{ name }}', ['name' => '<br>world']));
    }

    public function testRenderWithMissingFunction(): void
    {
        $template = new Template(null, ['capitalize' => 'ucfirst']);
        $this->assertEquals("hello {{name|failure!!filter `failure` not found}}", $template->render('hello {{ name|failure }}', ['name' => 'world']));
    }

    public function testRenderWithFunctionLiteralArgument(): void
    {
        $template = new Template(null, ['dateFormat' => fn(string $date, string $format) => date($format, strtotime($date) ?: null)]);
        $this->assertEquals("hello 1980-05-13", $template->render('hello {{ name|dateFormat("Y-m-d") }}', ['name' => 'May 13, 1980']));
    }

    public function testRenderWithFunctionDataArgument(): void
    {
        $template = new Template(null, ['dateFormat' => fn(string $date, string $format) => date($format, strtotime($date) ?: null)]);
        $this->assertEquals("hello 1980-05-13", $template->render('hello {{ name|dateFormat(format) }}', ['name' => 'May 13, 1980', 'format' => 'Y-m-d']));
    }

    public function testRenderWithFunctionComplexLiteralArgument(): void
    {
        $template = new Template(null, ['dateFormat' => fn(string $date, string $format) => date($format, strtotime($date) ?: null)]);
        $this->assertEquals("hello May 13, 1980", $template->render('hello {{ name|dateFormat("M j, Y") }}', ['name' => 'May 13, 1980']));
    }

    public function testRenderWithFunctionArgumentWithWhitespace(): void
    {
        $template = new Template(null, ['dateFormat' => fn(string $date, string $format) => date($format, strtotime($date) ?: null)]);
        $this->assertEquals("hello May 13, 1980", $template->render('hello {{ name|dateFormat( "M j, Y") }}', ['name' => 'May 13, 1980']));
    }

    public function testRenderWithEscapedSpecialCharacters(): void
    {
        $template = new Template(null, ['dateFormat' => fn(string $date, string $format) => date($format, strtotime($date) ?: null)]);
        $this->assertEquals("hello \" May ()}}&quot;,|:.13, 1980\"", $template->render('hello "{{ name|dateFormat(" M ()}}\\",|:.j, Y") }}"', ['name' => 'May 13, 1980']));
    }

    public function testEscape(): void
    {
        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', self::$template->render('{{ a }}', ['a' => '<script>alert("xss")</script>']));
    }

    public function testRawEscape(): void
    {
        $this->assertEquals('<script>alert("xss")</script>', self::$template->render('{{ a|raw }}', ['a' => '<script>alert("xss")</script>']));
    }

    public function testNoEscape(): void
    {
        // Since HTML escaping is now always enabled, use raw filter to bypass escaping
        $template = new Template();
        $this->assertEquals('<script>alert("xss")</script>', $template->render('{{ a|raw }}', ['a' => '<script>alert("xss")</script>']));
    }

    // Comment syntax tests - {# ... #}
    public function testCommentSimple(): void
    {
        $this->assertEquals("hello  world", self::$template->render('hello {# comment #} world', []));
    }

    public function testCommentWithVariables(): void
    {
        $this->assertEquals("hello  test world", self::$template->render('hello {# this is ignored #} {{ text }} world', ['text' => 'test']));
    }

    public function testCommentMultiline(): void
    {
        $template = "Line 1\n{# This is\na multiline\ncomment #}\nLine 2";
        $expected = "Line 1\nLine 2";
        $this->assertEquals($expected, self::$template->render($template, []));
    }

    public function testCommentWithControlStructures(): void
    {
        $this->assertEquals("result", self::$template->render('{# comment #}{% if true %}result{% endif %}{# another #}', ['true' => true]));
    }

    public function testCommentMultiple(): void
    {
        $this->assertEquals("abc", self::$template->render('a{# one #}b{# two #}c{# three #}', []));
    }

    public function testCommentWithSpecialChars(): void
    {
        $this->assertEquals("text", self::$template->render('{# {{ }} {% %} #}text', []));
    }

    public function testCommentInTemplate(): void
    {
        $template = "{# Header comment #}\n<div>\n    {# Content comment #}\n    {{ content }}\n</div>\n{# Footer comment #}";
        $expected = "<div>\n    Data\n</div>\n";
        $this->assertEquals($expected, self::$template->render($template, ['content' => 'Data']));
    }

    public function testCommentBeforeAndAfterVariable(): void
    {
        $this->assertEquals("Value", self::$template->render('{# before #}{{ text }}{# after #}', ['text' => 'Value']));
    }

    public function testCommentInForLoop(): void
    {
        $template = "{% for i in items %}{# loop comment #}{{ i }}{% endfor %}";
        $expected = "123";
        $this->assertEquals($expected, self::$template->render($template, ['items' => [1, 2, 3]]));
    }

    public function testCommentEmpty(): void
    {
        $this->assertEquals("text", self::$template->render('{##}text', []));
    }

    // Newlines in expressions tests
    public function testExpressionWithNewlineInVariable(): void
    {
        $template = "{{ a\n+ b }}";
        $this->assertEquals("15", self::$template->render($template, ['a' => 10, 'b' => 5]));
    }

    public function testExpressionWithMultipleNewlinesInVariable(): void
    {
        $template = "{{ a\n+\nb\n*\nc }}";
        $this->assertEquals("14", self::$template->render($template, ['a' => 2, 'b' => 3, 'c' => 4]));
    }

    public function testExpressionWithNewlineInIfCondition(): void
    {
        $template = "{% if a\n>\n5 %}yes{% endif %}";
        $this->assertEquals("yes", self::$template->render($template, ['a' => 10]));
    }

    public function testExpressionWithNewlineInComplexCondition(): void
    {
        $template = "{% if a\n>\n5\n&&\nb\n<\n20 %}match{% endif %}";
        $this->assertEquals("match", self::$template->render($template, ['a' => 10, 'b' => 15]));
    }

    public function testExpressionWithNewlineInParentheses(): void
    {
        $template = "{{ (\na\n+\nb\n)\n*\nc }}";
        $this->assertEquals("18", self::$template->render($template, ['a' => 2, 'b' => 4, 'c' => 3]));
    }

    public function testExpressionWithNewlineInComparison(): void
    {
        $template = "{% if a\n==\n5 %}equal{% endif %}";
        $this->assertEquals("equal", self::$template->render($template, ['a' => 5]));
    }

    public function testExpressionWithNewlineInLogicalOperators(): void
    {
        $template = "{% if a\nand\nb\nor\nc %}yes{% endif %}";
        $this->assertEquals("yes", self::$template->render($template, ['a' => false, 'b' => false, 'c' => true]));
    }

    public function testExpressionWithNewlineInForLoop(): void
    {
        $template = "{% for i\nin\nitems %}{{ i }}{% endfor %}";
        $this->assertEquals("123", self::$template->render($template, ['items' => [1, 2, 3]]));
    }

    public function testExpressionWithNewlineInStringConcatenationAndInString(): void
    {
        $template = "{{ first\n+\n\"\n\"\n+\nsecond }}";
        $this->assertEquals("hello\nworld", self::$template->render($template, ['first' => 'hello', 'second' => 'world']));
    }

    public function testExpressionWithNewlineBeforeFilter(): void
    {
        $template = new Template(null, ['capitalize' => 'ucfirst']);
        $result = $template->render("{{ name\n|capitalize }}", ['name' => 'world']);
        $this->assertEquals("World", $result);
    }

    public function testExpressionWithNewlineInFilterArguments(): void
    {
        $template = new Template(null, ['dateFormat' => fn(string $date, string $format) => date($format, strtotime($date) ?: null)]);
        $result = $template->render("{{ name\n|dateFormat(\n\"Y-m-d\"\n) }}", ['name' => 'May 13, 1980']);
        $this->assertEquals("1980-05-13", $result);
    }

    public function testExpressionWithCarriageReturnNewline(): void
    {
        $template = "{{ a\r\n+\r\nb }}";
        $this->assertEquals("15", self::$template->render($template, ['a' => 10, 'b' => 5]));
    }

    public function testExpressionWithMixedWhitespaceAndNewlines(): void
    {
        $template = "{{ a  \n  +  \n  b  \n  *  \n  c }}";
        $this->assertEquals("14", self::$template->render($template, ['a' => 2, 'b' => 3, 'c' => 4]));
    }

    public function testExpressionWithNewlineInElseIfCondition(): void
    {
        $template = "{% if a\n>\n10 %}first{% elseif a\n>\n5 %}second{% else %}third{% endif %}";
        $this->assertEquals("second", self::$template->render($template, ['a' => 7]));
    }
}
