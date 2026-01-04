<?php

namespace MintyPHP\Template\Tests;

use MintyPHP\Template\Template;
use PHPUnit\Framework\TestCase;

class FiltersTest extends TestCase
{
    private static Template $template;

    public static function setUpBeforeClass(): void
    {
        self::$template = new Template();
    }

    // String filter tests

    public function testFilterLower(): void
    {
        $result = self::$template->render('{{ text|lower }}', ['text' => 'HELLO WORLD']);
        $this->assertEquals('hello world', $result);
    }

    public function testFilterUpper(): void
    {
        $result = self::$template->render('{{ text|upper }}', ['text' => 'hello world']);
        $this->assertEquals('HELLO WORLD', $result);
    }

    public function testFilterCapitalize(): void
    {
        $result = self::$template->render('{{ text|capitalize }}', ['text' => 'hello world']);
        $this->assertEquals('Hello world', $result);
    }

    public function testFilterTitle(): void
    {
        $result = self::$template->render('{{ text|title }}', ['text' => 'hello world']);
        $this->assertEquals('Hello World', $result);
    }

    public function testFilterTrim(): void
    {
        $result = self::$template->render('{{ text|trim }}', ['text' => '  hello  ']);
        $this->assertEquals('hello', $result);
    }

    public function testFilterTruncate(): void
    {
        $result = self::$template->render('{{ text|truncate(8) }}', ['text' => 'Hello World']);
        $this->assertEquals('Hello...', $result);
    }

    public function testFilterTruncateCustomEnd(): void
    {
        $result = self::$template->render('{{ text|truncate(10, "..") }}', ['text' => 'Hello World']);
        $this->assertEquals('Hello..', $result);
    }

    public function testFilterTruncateNoTruncation(): void
    {
        $result = self::$template->render('{{ text|truncate(20) }}', ['text' => 'Hello']);
        $this->assertEquals('Hello', $result);
    }

    public function testFilterTruncateLongWord(): void
    {
        $result = self::$template->render('{{ text|truncate(10) }}', ['text' => 'Supercalifragilistic']);
        $this->assertEquals('Superca...', $result);
    }

    public function testFilterTruncateMultipleWords(): void
    {
        $result = self::$template->render('{{ text|truncate(20) }}', ['text' => 'The quick brown fox jumps']);
        $this->assertEquals('The quick brown...', $result);
    }

    public function testFilterTruncateWithTrailingSpace(): void
    {
        $result = self::$template->render('{{ text|truncate(15) }}', ['text' => 'Hello world and more']);
        $this->assertEquals('Hello world...', $result);
    }

    public function testFilterReplace(): void
    {
        $result = self::$template->render('{{ text|replace("Hello", "Goodbye") }}', ['text' => 'Hello World']);
        $this->assertEquals('Goodbye World', $result);
    }

    public function testFilterReplaceWithCount(): void
    {
        $result = self::$template->render('{{ text|replace("a", "o", 2) }}', ['text' => 'banana']);
        $this->assertEquals('bonona', $result);
    }

    public function testFilterSplit(): void
    {
        $result = self::$template->render('{{ text|split(",")|join("|") }}', ['text' => '1,2,3']);
        $this->assertEquals('1|2|3', $result);
    }

    public function testFilterSplitChars(): void
    {
        $result = self::$template->render('{{ text|split|join("|") }}', ['text' => 'abc']);
        $this->assertEquals('a|b|c', $result);
    }

    public function testFilterURLEncode(): void
    {
        $result = self::$template->render('{{ text|urlencode }}', ['text' => 'hello world']);
        $this->assertEquals('hello+world', $result);
    }

    public function testFilterURLEncodeSpecialChars(): void
    {
        $result = self::$template->render('{{ text|urlencode }}', ['text' => 'hello&world=test']);
        $this->assertEquals('hello%26world%3Dtest', $result);
    }

    // Numeric filter tests

    public function testFilterAbs(): void
    {
        $result = self::$template->render('{{ num|abs }}', ['num' => -42]);
        $this->assertEquals('42', $result);
    }

    public function testFilterAbsPositive(): void
    {
        $result = self::$template->render('{{ num|abs }}', ['num' => 42]);
        $this->assertEquals('42', $result);
    }

    public function testFilterRound(): void
    {
        $result = self::$template->render('{{ num|round }}', ['num' => 42.55]);
        $this->assertEquals('43', $result);
    }

    public function testFilterRoundWithPrecision(): void
    {
        $result = self::$template->render('{{ num|round(1, "floor") }}', ['num' => 42.55]);
        $this->assertEquals('42.5', $result);
    }

    public function testFilterRoundCeil(): void
    {
        $result = self::$template->render('{{ num|round(0, "ceil") }}', ['num' => 42.1]);
        $this->assertEquals('43', $result);
    }

    public function testFilterSprintf(): void
    {
        $result = self::$template->render('{{ num|sprintf("%.2f") }}', ['num' => 3.14159]);
        $this->assertEquals('3.14', $result);
    }

    public function testFilterFileSizeFormat(): void
    {
        $result = self::$template->render('{{ size|filesizeformat }}', ['size' => 13000]);
        $this->assertEquals('13.0 kB', $result);
    }

    public function testFilterFileSizeFormatBinary(): void
    {
        $result = self::$template->render('{{ size|filesizeformat(true) }}', ['size' => 1024]);
        $this->assertEquals('1.0 KiB', $result);
    }

    public function testFilterFileSizeFormatLarge(): void
    {
        $result = self::$template->render('{{ size|filesizeformat }}', ['size' => 1500000]);
        $this->assertEquals('1.5 MB', $result);
    }

    // Array/Collection filter tests

    public function testFilterLength(): void
    {
        $result = self::$template->render('{{ items|length }}', ['items' => [1, 2, 3]]);
        $this->assertEquals('3', $result);
    }

    public function testFilterCount(): void
    {
        $result = self::$template->render('{{ items|count }}', ['items' => [1, 2, 3, 4]]);
        $this->assertEquals('4', $result);
    }

    public function testFilterLengthString(): void
    {
        $result = self::$template->render('{{ text|length }}', ['text' => 'hello']);
        $this->assertEquals('5', $result);
    }

    public function testFilterFirst(): void
    {
        $result = self::$template->render('{{ items|first }}', ['items' => [1, 2, 3, 4]]);
        $this->assertEquals('1', $result);
    }

    public function testFilterFirstMultiple(): void
    {
        $result = self::$template->render('{{ items|first(2)|join(",") }}', ['items' => [1, 2, 3, 4]]);
        $this->assertEquals('1,2', $result);
    }

    public function testFilterLast(): void
    {
        $result = self::$template->render('{{ items|last }}', ['items' => [1, 2, 3, 4]]);
        $this->assertEquals('4', $result);
    }

    public function testFilterLastMultiple(): void
    {
        $result = self::$template->render('{{ items|last(2)|join(",") }}', ['items' => [1, 2, 3, 4]]);
        $this->assertEquals('3,4', $result);
    }

    public function testFilterJoin(): void
    {
        $result = self::$template->render('{{ items|join("|") }}', ['items' => [1, 2, 3]]);
        $this->assertEquals('1|2|3', $result);
    }

    public function testFilterJoinNoSeparator(): void
    {
        $result = self::$template->render('{{ items|join }}', ['items' => [1, 2, 3]]);
        $this->assertEquals('123', $result);
    }

    public function testFilterJoinAttribute(): void
    {
        $users = [
            ['name' => 'Alice'],
            ['name' => 'Bob'],
        ];
        $result = self::$template->render('{{ users|join(", ", "name") }}', ['users' => $users]);
        $this->assertEquals('Alice, Bob', $result);
    }

    public function testFilterReverse(): void
    {
        $result = self::$template->render('{{ items|reverse|join(",") }}', ['items' => [1, 2, 3]]);
        $this->assertEquals('3,2,1', $result);
    }

    public function testFilterReverseString(): void
    {
        $result = self::$template->render('{{ text|reverse }}', ['text' => 'hello']);
        $this->assertEquals('olleh', $result);
    }

    public function testFilterSum(): void
    {
        $result = self::$template->render('{{ items|sum }}', ['items' => [1, 2, 3]]);
        $this->assertEquals('6', $result);
    }

    public function testFilterSumAttribute(): void
    {
        $items = [
            ['price' => 10],
            ['price' => 20],
            ['price' => 30],
        ];
        $result = self::$template->render('{{ items|sum("price") }}', ['items' => $items]);
        $this->assertEquals('60', $result);
    }

    // Utility filter tests

    public function testFilterDefault(): void
    {
        $result = self::$template->render('{{ value|default("N/A") }}', ['value' => null]);
        $this->assertEquals('N/A', $result);
    }

    public function testFilterDefaultWithValue(): void
    {
        $result = self::$template->render('{{ value|default("N/A") }}', ['value' => 'exists']);
        $this->assertEquals('exists', $result);
    }

    public function testFilterDefaultBoolean(): void
    {
        $result = self::$template->render('{{ value|default("empty", true) }}', ['value' => '']);
        $this->assertEquals('empty', $result);
    }

    public function testFilterDefaultBooleanZero(): void
    {
        $result = self::$template->render('{{ value|default("zero", true) }}', ['value' => 0]);
        $this->assertEquals('zero', $result);
    }

    public function testFilterAttr(): void
    {
        $data = [
            'user' => [
                'name' => 'Alice',
                'email' => 'alice@example.com',
            ],
        ];
        $result = self::$template->render('{{ user|attr("email") }}', $data);
        $this->assertEquals('alice@example.com', $result);
    }

    public function testFilterAttrMissing(): void
    {
        $data = [
            'user' => [
                'name' => 'Alice',
            ],
        ];
        $result = self::$template->render('{{ user|attr("missing") }}', $data);
        $this->assertEquals('', $result);
    }

    public function testFilterDebug(): void
    {
        $data = [
            'user' => [
                'name' => 'Alice',
                'age' => 30,
            ],
        ];
        $result = self::$template->render('{{ user|debug|raw }}', $data);
        $this->assertStringContainsString('"name"', $result);
        $this->assertStringContainsString('"Alice"', $result);
    }

    public function testFilterDebugAlias(): void
    {
        $result = self::$template->render('{{ value|d }}', ['value' => 42]);
        $this->assertEquals('42', $result);
    }

    public function testFilterRaw(): void
    {
        $result = self::$template->render('{{ html|raw }}', ['html' => '<strong>Bold</strong>']);
        $this->assertEquals('<strong>Bold</strong>', $result);
    }

    public function testFilterRawWithoutEscaping(): void
    {
        $result = self::$template->render('{{ html }}', ['html' => '<strong>Bold</strong>']);
        $this->assertEquals('&lt;strong&gt;Bold&lt;/strong&gt;', $result);
    }

    // Filter chaining tests

    public function testFilterChaining(): void
    {
        $result = self::$template->render('{{ text|trim|upper|replace("WORLD", "FRIEND") }}', ['text' => '  hello world  ']);
        $this->assertEquals('HELLO FRIEND', $result);
    }

    public function testFilterChainingArrays(): void
    {
        $result = self::$template->render('{{ items|first(3)|reverse|join(", ") }}', ['items' => [1, 2, 3, 4, 5]]);
        $this->assertEquals('3, 2, 1', $result);
    }

    public function testFilterChainingComplex(): void
    {
        $users = [
            ['name' => 'alice'],
            ['name' => 'bob'],
            ['name' => 'charlie'],
        ];
        $result = self::$template->render('{{ users|join(", ", "name")|upper }}', ['users' => $users]);
        $this->assertEquals('ALICE, BOB, CHARLIE', $result);
    }

    // Edge case tests

    public function testFilterEmptyArray(): void
    {
        $result = self::$template->render('{{ items|length }}', ['items' => []]);
        $this->assertEquals('0', $result);
    }

    public function testFilterEmptyString(): void
    {
        $result = self::$template->render('{{ text|upper }}', ['text' => '']);
        $this->assertEquals('', $result);
    }

    public function testFilterNilValue(): void
    {
        $result = self::$template->render('{{ value|default("nil") }}', ['value' => null]);
        $this->assertEquals('nil', $result);
    }

    public function testFilterNumericString(): void
    {
        $result = self::$template->render('{{ num|abs }}', ['num' => '-42']);
        $this->assertEquals('42', $result);
    }
}
