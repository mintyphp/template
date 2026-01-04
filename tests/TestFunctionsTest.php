<?php

namespace MintyPHP\Template\Tests;

use MintyPHP\Template\Template;
use PHPUnit\Framework\TestCase;

class TestFunctionsTest extends TestCase
{
    private static Template $template;

    public static function setUpBeforeClass(): void
    {
        self::$template = new Template();
    }

    public function testIsDefined(): void
    {
        $tmpl = "{% if variable is defined %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['variable' => 'value']);
        $this->assertEquals("yes", $result);

        // Test undefined variable
        $tmpl = "{% if missing is defined %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, []);
        $this->assertEquals("no", $result);
    }

    public function testIsUndefined(): void
    {
        $tmpl = "{% if missing is undefined %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, []);
        $this->assertEquals("yes", $result);

        // Test defined variable
        $tmpl = "{% if variable is undefined %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['variable' => 'value']);
        $this->assertEquals("no", $result);
    }

    public function testIsEven(): void
    {
        $tmpl = "{% if num is even %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['num' => 4]);
        $this->assertEquals("yes", $result);

        // Test odd number
        $result = self::$template->render($tmpl, ['num' => 3]);
        $this->assertEquals("no", $result);
    }

    public function testIsOdd(): void
    {
        $tmpl = "{% if num is odd %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['num' => 3]);
        $this->assertEquals("yes", $result);

        // Test even number
        $result = self::$template->render($tmpl, ['num' => 4]);
        $this->assertEquals("no", $result);
    }

    public function testIsDivisibleBy(): void
    {
        $tmpl = "{% if num is divisibleby(3) %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['num' => 9]);
        $this->assertEquals("yes", $result);

        // Test not divisible
        $result = self::$template->render($tmpl, ['num' => 10]);
        $this->assertEquals("no", $result);

        // Test divisible by 2
        $tmpl = "{% if num is divisibleby(2) %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['num' => 10]);
        $this->assertEquals("yes", $result);
    }

    public function testIsIterable(): void
    {
        $tmpl = "{% if items is iterable %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['items' => [1, 2, 3]]);
        $this->assertEquals("yes", $result);

        // Test non-iterable
        $result = self::$template->render($tmpl, ['items' => 42]);
        $this->assertEquals("no", $result);

        // Test string is iterable
        $result = self::$template->render($tmpl, ['items' => 'hello']);
        $this->assertEquals("yes", $result);
    }

    public function testIsNull(): void
    {
        $tmpl = "{% if value is null %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['value' => null]);
        $this->assertEquals("yes", $result);

        // Test non-null
        $result = self::$template->render($tmpl, ['value' => 'something']);
        $this->assertEquals("no", $result);
    }

    public function testIsNumber(): void
    {
        $tmpl = "{% if value is number %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['value' => 42]);
        $this->assertEquals("yes", $result);

        // Test float
        $result = self::$template->render($tmpl, ['value' => 3.14]);
        $this->assertEquals("yes", $result);

        // Test string number
        $result = self::$template->render($tmpl, ['value' => '123']);
        $this->assertEquals("yes", $result);

        // Test non-number string
        $result = self::$template->render($tmpl, ['value' => 'abc']);
        $this->assertEquals("no", $result);
    }

    public function testIsString(): void
    {
        $tmpl = "{% if value is string %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['value' => 'hello']);
        $this->assertEquals("yes", $result);

        // Test number
        $result = self::$template->render($tmpl, ['value' => 42]);
        $this->assertEquals("no", $result);
    }

    public function testIsNotTest(): void
    {
        $tmpl = "{% if value is not null %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['value' => 'something']);
        $this->assertEquals("yes", $result);

        // Test with null value
        $result = self::$template->render($tmpl, ['value' => null]);
        $this->assertEquals("no", $result);
    }

    public function testIsTestInVariable(): void
    {
        $tmpl = "{{ num is even }}";
        $result = self::$template->render($tmpl, ['num' => 4]);
        $this->assertEquals("1", $result);

        // Test false case
        $result = self::$template->render($tmpl, ['num' => 3]);
        $this->assertEquals("", $result);
    }

    public function testIsTestWithComplexExpression(): void
    {
        $tmpl = "{% if (value + 1) is even %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['value' => 3]);
        $this->assertEquals("yes", $result);
    }

    public function testMultipleIsTests(): void
    {
        $tmpl = "{% if value is number and value is even %}yes{% else %}no{% endif %}";
        $result = self::$template->render($tmpl, ['value' => 4]);
        $this->assertEquals("yes", $result);
    }
}
