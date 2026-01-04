<?php

namespace MintyPHP\Template\Tests;

use MintyPHP\Template\Template;
use PHPUnit\Framework\TestCase;

class ControlStructuresTest extends TestCase
{
    private static Template $template;

    public static function setUpBeforeClass(): void
    {
        self::$template = new Template();
    }

    public function testRenderIfWithNestedPath(): void
    {
        $this->assertEquals("hello m is 3", self::$template->render('hello {% if n.m|eq(3) %}m is 3{% endif %}', ['n' => ['m' => 3]], ['eq' => fn($a, $b) => $a == $b]));
    }

    public function testRenderIfElse(): void
    {
        $this->assertEquals("hello not n", self::$template->render('hello {% if n %}n{% else %}not n{% endif %}', ['n' => false]));
    }

    public function testRenderForLoopWithValues(): void
    {
        $this->assertEquals("test 1 2 3", self::$template->render('test{% for i in counts %} {{ i }}{% endfor %}', ['counts' => [1, 2, 3]]));
    }

    public function testRenderForLoopWithKeysAndValues(): void
    {
        $this->assertEquals("test a=1 b=2 c=3", self::$template->render('test{% for k, v in counts %} {{ k }}={{ v }}{% endfor %}', ['counts' => ['a' => 1, 'b' => 2, 'c' => 3]]));
    }

    public function testRenderNestedForLoops(): void
    {
        $this->assertEquals("test (-1,-1) (-1,1) (1,-1) (1,1)", self::$template->render('test{% for x in steps %}{% for y in steps %} ({{ x }},{{ y }}){% endfor %}{% endfor %}', ['steps' => [-1, 1]]));
    }

    public function testRenderForLoopWithIfElseIf(): void
    {
        $this->assertEquals("hello one two three", self::$template->render('hello{% for i in counts %} {% if i|eq(1) %}one{% elseif i|eq(2) %}two{% else %}three{% endif %}{% endfor %}', ['counts' => [1, 2, 3]], ['eq' => fn($a, $b) => $a == $b]));
    }

    // Multiline template tests inspired by Jinja
    public function testMultilineForLoopSimple(): void
    {
        $template = "<ul>\n{% for item in items %}\n    <li>{{ item }}</li>\n{% endfor %}\n</ul>";
        $expected = "<ul>\n    <li>apple</li>\n    <li>banana</li>\n    <li>cherry</li>\n</ul>";
        $this->assertEquals($expected, self::$template->render($template, ['items' => ['apple', 'banana', 'cherry']]));
    }

    public function testMultilineForLoopWithIndentation(): void
    {
        $template = "<div>\n    <ul>\n    {% for user in users %}\n        <li>{{ user }}</li>\n    {% endfor %}\n    </ul>\n</div>";
        $expected = "<div>\n    <ul>\n        <li>Alice</li>\n        <li>Bob</li>\n    </ul>\n</div>";
        $this->assertEquals($expected, self::$template->render($template, ['users' => ['Alice', 'Bob']]));
    }

    public function testMultilineIfWithWhitespace(): void
    {
        $template = "<div>\n    {% if active %}\n        <span>Active</span>\n    {% endif %}\n</div>";
        $expected = "<div>\n        <span>Active</span>\n</div>";
        $this->assertEquals($expected, self::$template->render($template, ['active' => true]));
    }

    public function testMultilineIfElseWithWhitespace(): void
    {
        $template = "<div>\n    {% if active %}\n        <span>Active</span>\n    {% else %}\n        <span>Inactive</span>\n    {% endif %}\n</div>";
        $expected = "<div>\n        <span>Inactive</span>\n</div>";
        $this->assertEquals($expected, self::$template->render($template, ['active' => false]));
    }

    public function testMultilineNestedForLoops(): void
    {
        $template = "<table>\n{% for row in rows %}\n    <tr>\n    {% for cell in row %}\n        <td>{{ cell }}</td>\n    {% endfor %}\n    </tr>\n{% endfor %}\n</table>";
        $expected = "<table>\n    <tr>\n        <td>1</td>\n        <td>2</td>\n    </tr>\n    <tr>\n        <td>3</td>\n        <td>4</td>\n    </tr>\n</table>";
        $this->assertEquals($expected, self::$template->render($template, ['rows' => [[1, 2], [3, 4]]]));
    }

    public function testMultilineComplexHtmlStructure(): void
    {
        $template = "<!DOCTYPE html>\n<html>\n<head>\n    <title>{{ title }}</title>\n</head>\n<body>\n    <ul id=\"navigation\">\n    {% for item in navigation %}\n        <li><a href=\"{{ item.href }}\">{{ item.caption }}</a></li>\n    {% endfor %}\n    </ul>\n    <h1>{{ heading }}</h1>\n</body>\n</html>";

        $data = [
            'title' => 'My Page',
            'heading' => 'Welcome',
            'navigation' => [
                ['href' => '/home', 'caption' => 'Home'],
                ['href' => '/about', 'caption' => 'About']
            ]
        ];

        $expected = "<!DOCTYPE html>\n<html>\n<head>\n    <title>My Page</title>\n</head>\n<body>\n    <ul id=\"navigation\">\n        <li><a href=\"/home\">Home</a></li>\n        <li><a href=\"/about\">About</a></li>\n    </ul>\n    <h1>Welcome</h1>\n</body>\n</html>";

        $this->assertEquals($expected, self::$template->render($template, $data));
    }

    public function testWhitespacePreservationWithLeadingSpaces(): void
    {
        $template = "    Leading spaces\n{{ text }}\n    Trailing spaces    ";
        $expected = "    Leading spaces\nHello\n    Trailing spaces    ";
        $this->assertEquals($expected, self::$template->render($template, ['text' => 'Hello']));
    }

    public function testWhitespacePreservationWithTabs(): void
    {
        $template = "\t\tTabbed content\n{{ text }}\n\t\tMore tabs";
        $expected = "\t\tTabbed content\nWorld\n\t\tMore tabs";
        $this->assertEquals($expected, self::$template->render($template, ['text' => 'World']));
    }

    public function testWhitespacePreservationEmptyLines(): void
    {
        $template = "Line 1\n\n{{ text }}\n\nLine 4";
        $expected = "Line 1\n\nTest\n\nLine 4";
        $this->assertEquals($expected, self::$template->render($template, ['text' => 'Test']));
    }

    public function testMultilineForLoopWithEmptyList(): void
    {
        $template = "<ul>\n{% for item in items %}\n    <li>{{ item }}</li>\n{% endfor %}\n</ul>";
        $expected = "<ul>\n</ul>";
        $this->assertEquals($expected, self::$template->render($template, ['items' => []]));
    }

    public function testMultilineIfWithFalseCondition(): void
    {
        $template = "<div>\n    Content before\n    {% if show %}\n        This should not appear\n    {% endif %}\n    Content after\n</div>";
        $expected = "<div>\n    Content before\n    Content after\n</div>";
        $this->assertEquals($expected, self::$template->render($template, ['show' => false]));
    }

    public function testMultilineTextPreservation(): void
    {
        $template = "First line\nSecond line\nThird line with {{ var }}\nFourth line";
        $expected = "First line\nSecond line\nThird line with value\nFourth line";
        $this->assertEquals($expected, self::$template->render($template, ['var' => 'value']));
    }

    public function testMultilineWithMixedContentTypes(): void
    {
        $template = "<p>\n    Text content\n    {{ text }}\n    {% if show %}\n        <strong>{{ emphasis }}</strong>\n    {% endif %}\n    More text\n</p>";
        $expected = "<p>\n    Text content\n    Hello\n        <strong>Important</strong>\n    More text\n</p>";
        $this->assertEquals($expected, self::$template->render($template, ['text' => 'Hello', 'show' => true, 'emphasis' => 'Important']));
    }

    public function testMultilineHtmlListWithData(): void
    {
        $template = "<h1>Members</h1>\n<ul>\n{% for user in users %}\n  <li>{{ user.username }}</li>\n{% endfor %}\n</ul>";
        $expected = "<h1>Members</h1>\n<ul>\n  <li>alice</li>\n  <li>bob</li>\n  <li>charlie</li>\n</ul>";
        $data = [
            'users' => [
                ['username' => 'alice'],
                ['username' => 'bob'],
                ['username' => 'charlie']
            ]
        ];
        $this->assertEquals($expected, self::$template->render($template, $data));
    }

    public function testMultilineNestedIfStatements(): void
    {
        $template = "<div>\n{% if outer %}\n    <div class=\"outer\">\n    {% if inner %}\n        <div class=\"inner\">Content</div>\n    {% endif %}\n    </div>\n{% endif %}\n</div>";
        $expected = "<div>\n    <div class=\"outer\">\n        <div class=\"inner\">Content</div>\n    </div>\n</div>";
        $this->assertEquals($expected, self::$template->render($template, ['outer' => true, 'inner' => true]));
    }

    public function testMultilineWhitespaceOnlyBetweenTags(): void
    {
        $template = "<div>   \n   {{ text }}   \n   </div>";
        $expected = "<div>   \n   Value   \n   </div>";
        $this->assertEquals($expected, self::$template->render($template, ['text' => 'Value']));
    }

    public function testMultilineCommentLikeStructure(): void
    {
        // Test Jinja-style {# #} comment syntax - comments should be completely removed
        $template = "<div>\n    {# This is a comment #}\n    {{ content }}\n    {# Another comment #}\n</div>";
        $expected = "<div>\n    Data\n</div>";
        $this->assertEquals($expected, self::$template->render($template, ['content' => 'Data']));
    }

    public function testMultilineForLoopWithComplexData(): void
    {
        $template = "<dl>\n{% for item in items %}\n  <dt>{{ item.key }}</dt>\n  <dd>{{ item.value }}</dd>\n{% endfor %}\n</dl>";
        $expected = "<dl>\n  <dt>Name</dt>\n  <dd>John</dd>\n  <dt>Age</dt>\n  <dd>30</dd>\n</dl>";
        $data = [
            'items' => [
                ['key' => 'Name', 'value' => 'John'],
                ['key' => 'Age', 'value' => '30']
            ]
        ];
        $this->assertEquals($expected, self::$template->render($template, $data));
    }

    public function testMultilineTemplateWithNoWhitespace(): void
    {
        $template = "<ul>{% for i in items %}<li>{{ i }}</li>{% endfor %}</ul>";
        $expected = "<ul><li>A</li><li>B</li></ul>";
        $this->assertEquals($expected, self::$template->render($template, ['items' => ['A', 'B']]));
    }

    public function testMultilineIndentationVariations(): void
    {
        $template = "<div>\n  Two spaces\n    Four spaces\n\tOne tab\n{{ text }}\n</div>";
        $expected = "<div>\n  Two spaces\n    Four spaces\n\tOne tab\nValue\n</div>";
        $this->assertEquals($expected, self::$template->render($template, ['text' => 'Value']));
    }
}
