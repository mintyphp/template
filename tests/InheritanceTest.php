<?php

namespace MintyPHP\Template\Tests;

use MintyPHP\Template\Template;
use PHPUnit\Framework\TestCase;

class InheritanceTest extends TestCase
{
    private static Template $template;

    public static function setUpBeforeClass(): void
    {
        self::$template = new Template();
    }

    public function testBlockBasic(): void
    {
        $tmpl = "<html>\n{% block title %}Default Title{% endblock %}\n{% block content %}Default Content{% endblock %}\n</html>";
        $expected = "<html>\nDefault Title\nDefault Content\n</html>";

        $template = new Template();
        $result = $template->render($tmpl, []);
        $this->assertEquals($expected, $result);
    }

    public function testExtendsWithBlockOverride(): void
    {
        $templates = [
            'base.html' => "<html>\n<head>\n  <title>{% block title %}My Website{% endblock %}</title>\n</head>\n<body>\n  {% block content %}{% endblock %}\n</body>\n</html>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $childTmpl = "{% extends 'base.html' %}\n\n{% block title %}Home Page{% endblock %}\n\n{% block content %}\n<h1>Welcome to the home page!</h1>\n{% endblock %}";

        $expected = "<html>\n<head>\n  <title>Home Page</title>\n</head>\n<body>\n<h1>Welcome to the home page!</h1>\n</body>\n</html>";

        $template = new Template($loader);
        $result = $template->render($childTmpl, []);
        $this->assertEquals($expected, $result);
    }

    public function testExtendsWithPartialOverride(): void
    {
        $templates = [
            'base.html' => "<html>\n<head>\n  <title>{% block title %}Default Title{% endblock %}</title>\n</head>\n<body>\n  <header>{% block header %}Default Header{% endblock %}</header>\n  <main>{% block content %}Default Content{% endblock %}</main>\n  <footer>{% block footer %}Default Footer{% endblock %}</footer>\n</body>\n</html>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $childTmpl = "{% extends 'base.html' %}\n\n{% block title %}Custom Title{% endblock %}\n\n{% block content %}<p>Custom content here</p>\n{% endblock %}";

        $expected = "<html>\n<head>\n  <title>Custom Title</title>\n</head>\n<body>\n  <header>Default Header</header>\n  <main><p>Custom content here</p>\n</main>\n  <footer>Default Footer</footer>\n</body>\n</html>";

        $template = new Template($loader);
        $result = $template->render($childTmpl, []);
        $this->assertEquals($expected, $result);
    }

    public function testExtendsWithVariables(): void
    {
        $templates = [
            'base.html' => "<html>\n<head>\n  <title>{% block title %}{{ site_name }}{% endblock %}</title>\n</head>\n<body>\n  {% block content %}{% endblock %}\n</body>\n</html>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $childTmpl = "{% extends 'base.html' %}\n\n{% block title %}{{ page_title }} - {{ site_name }}{% endblock %}\n\n{% block content %}\n<h1>{{ heading }}</h1>\n<p>{{ message }}</p>\n{% endblock %}";

        $data = [
            'site_name' => 'My Site',
            'page_title' => 'About',
            'heading' => 'About Us',
            'message' => 'Welcome to our site!',
        ];

        $expected = "<html>\n<head>\n  <title>About - My Site</title>\n</head>\n<body>\n<h1>About Us</h1>\n<p>Welcome to our site!</p>\n</body>\n</html>";

        $template = new Template($loader);
        $result = $template->render($childTmpl, $data);
        $this->assertEquals($expected, $result);
    }

    public function testExtendsWithControlStructures(): void
    {
        $templates = [
            'base.html' => "<html>\n<body>\n  <ul>\n  {% block navigation %}\n    <li><a href=\"/\">Home</a></li>\n  {% endblock %}\n  </ul>\n  {% block content %}{% endblock %}\n</body>\n</html>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $childTmpl = "{% extends 'base.html' %}\n\n{% block navigation %}\n{% for item in menu %}\n    <li><a href=\"{{ item.url }}\">{{ item.title }}</a></li>\n{% endfor %}\n{% endblock %}\n\n{% block content %}\n<h1>{{ title }}</h1>\n{% if show_list %}\n<ul>\n{% for item in items %}\n  <li>{{ item }}</li>\n{% endfor %}\n</ul>\n{% endif %}\n{% endblock %}";

        $data = [
            'menu' => [
                ['url' => '/', 'title' => 'Home'],
                ['url' => '/about', 'title' => 'About'],
                ['url' => '/contact', 'title' => 'Contact'],
            ],
            'title' => 'My Page',
            'show_list' => true,
            'items' => ['Item 1', 'Item 2', 'Item 3'],
        ];

        $expected = "<html>\n<body>\n  <ul>\n    <li><a href=\"/\">Home</a></li>\n    <li><a href=\"/about\">About</a></li>\n    <li><a href=\"/contact\">Contact</a></li>\n  </ul>\n<h1>My Page</h1>\n<ul>\n  <li>Item 1</li>\n  <li>Item 2</li>\n  <li>Item 3</li>\n</ul>\n</body>\n</html>";

        $template = new Template($loader);
        $result = $template->render($childTmpl, $data);
        $this->assertEquals($expected, $result);
    }

    public function testExtendsWithoutLoader(): void
    {
        $childTmpl = "{% extends 'base.html' %}\n{% block content %}Test{% endblock %}";

        $template = new Template();
        $result = $template->render($childTmpl, []);
        $this->assertStringContainsString('template loader not configured', $result);
    }

    public function testExtendsTemplateNotFound(): void
    {
        $loader = function (string $name): ?string {
            return null;
        };

        $childTmpl = "{% extends 'nonexistent.html' %}\n{% block content %}Test{% endblock %}";

        $template = new Template($loader);
        $result = $template->render($childTmpl, []);
        $this->assertStringContainsString('template not found', $result);
    }

    public function testNestedBlocks(): void
    {
        $templates = [
            'base.html' => "<div>\n{% block outer %}\n  <div class=\"outer\">\n  {% block inner %}Inner default{% endblock %}\n  </div>\n{% endblock %}\n</div>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $childTmpl = "{% extends 'base.html' %}\n\n{% block inner %}Custom inner content{% endblock %}";

        $expected = "<div>\n  <div class=\"outer\">\nCustom inner content\n  </div>\n</div>";

        $template = new Template($loader);
        $result = $template->render($childTmpl, []);
        $this->assertEquals($expected, $result);
    }

    public function testEmptyBlocks(): void
    {
        $templates = [
            'base.html' => "<html>\n<head>{% block head %}{% endblock %}</head>\n<body>{% block body %}Default body{% endblock %}</body>\n</html>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $childTmpl = "{% extends 'base.html' %}\n\n{% block head %}<title>Page</title>{% endblock %}\n\n{% block body %}{% endblock %}";

        $expected = "<html>\n<head><title>Page</title></head>\n<body></body>\n</html>";

        $template = new Template($loader);
        $result = $template->render($childTmpl, []);
        $this->assertEquals($expected, $result);
    }

    public function testBlockInheritanceNoIndentationPreservation(): void
    {
        $templates = [
            'base.html' => "<html>\n  <body>\n    <div>\n      {% block content %}Default{% endblock %}\n    </div>\n  </body>\n</html>",
        ];

        $loader = function (string $name) use ($templates): ?string {
            return $templates[$name] ?? null;
        };

        $childTmpl = "{% extends 'base.html' %}\n\n{% block content %}<h1>Title</h1>\n<p>Text</p>{% endblock %}";

        $expected = "<html>\n  <body>\n    <div>\n<h1>Title</h1>\n<p>Text</p>\n    </div>\n  </body>\n</html>";

        $template = new Template($loader);
        $result = $template->render($childTmpl, []);
        $this->assertEquals($expected, $result);
    }
}
