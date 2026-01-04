<?php

namespace MintyPHP\Template\Tests;

use MintyPHP\Template\Template;
use PHPUnit\Framework\TestCase;

class IncludeTest extends TestCase
{
    private static Template $template;

    public static function setUpBeforeClass(): void
    {
        self::$template = new Template();
    }

    public function testIncludeBasic(): void
    {
        $templates = [
            'header.html' => '<header><h1>Site Header</h1></header>',
            'main.html' => "<div>{% include 'header.html' %}\n<main>Main content</main>\n</div>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $template = new Template($loader);
        $result = $template->render($templates['main.html'], []);

        $expected = "<div><header><h1>Site Header</h1></header>\n<main>Main content</main>\n</div>";
        $this->assertEquals($expected, $result);
    }

    public function testIncludeWithVariables(): void
    {
        $templates = [
            'greeting.html' => '<p>Hello, {{ name }}!</p>',
            'main.html' => "<div>{% include 'greeting.html' %}</div>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $data = ['name' => 'Alice'];

        $template = new Template($loader);
        $result = $template->render($templates['main.html'], $data);

        $expected = '<div><p>Hello, Alice!</p></div>';
        $this->assertEquals($expected, $result);
    }

    public function testMultipleIncludes(): void
    {
        $templates = [
            'header.html' => "<header>Header</header>\n",
            'footer.html' => "<footer>Footer</footer>\n",
            'main.html' => "{% include 'header.html' %}\n<main>Content</main>\n{% include 'footer.html' %}\n",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $template = new Template($loader);
        $result = $template->render($templates['main.html'], []);

        $expected = "<header>Header</header>\n<main>Content</main>\n<footer>Footer</footer>\n";
        $this->assertEquals($expected, $result);
    }

    public function testIncludeWithControlStructures(): void
    {
        $templates = [
            'item.html' => "{% for item in items %}<li>{{ item }}</li>\n{% endfor %}",
            'main.html' => "<ul>\n{% include 'item.html' %}</ul>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $data = ['items' => ['Apple', 'Banana', 'Cherry']];

        $template = new Template($loader);
        $result = $template->render($templates['main.html'], $data);

        $expected = "<ul>\n<li>Apple</li>\n<li>Banana</li>\n<li>Cherry</li>\n</ul>";
        $this->assertEquals($expected, $result);
    }

    public function testIncludeWithoutLoader(): void
    {
        $template = new Template();
        $result = $template->render("{% include 'header.html' %}", []);
        $this->assertStringContainsString('template loader not configured', $result);
    }

    public function testIncludeTemplateNotFound(): void
    {
        $loader = function (string $name): ?string {
            return null;
        };

        $template = new Template($loader);
        $result = $template->render("{% include 'missing.html' %}", []);
        $this->assertStringContainsString('template not found', $result);
    }

    public function testNestedIncludes(): void
    {
        $templates = [
            'deep.html' => '<span>Deep content</span>',
            'middle.html' => "<div>{% include 'deep.html' %}</div>",
            'top.html' => "<section>{% include 'middle.html' %}</section>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $template = new Template($loader);
        $result = $template->render($templates['top.html'], []);

        $expected = '<section><div><span>Deep content</span></div></section>';
        $this->assertEquals($expected, $result);
    }
}
