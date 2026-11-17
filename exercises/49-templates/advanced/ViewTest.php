<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'view.php');

final class ViewTest extends TestCase
{
    private View $view;

    protected function setUp(): void
    {
        $this->view = new View(__DIR__ . '/templates');
    }

    public function testRenderCallsTheTemplateWithTheData(): void
    {
        self::assertSame("<p>Hello, Tom &amp; Jerry!</p>\n", $this->view->render('hello', ['name' => 'Tom & Jerry']));
    }

    public function testAPageIsTheTemplateInTheLayout(): void
    {
        $html = $this->view->page('A & B', 'hello', ['name' => 'Amira']);

        self::assertSame("<title>A &amp; B</title>\n<main><p>Hello, Amira!</p>\n</main>\n", $html);
    }

    /** @return iterable<string, array{string}> */
    public static function badNames(): iterable
    {
        yield 'a folder up' => ['../view'];
        yield 'empty' => [''];
        yield 'with .php' => ['hello.php'];
        yield 'no such template' => ['goodbye'];
    }

    #[DataProvider('badNames')]
    public function testOnlyNamesOfTemplatesAreLoaded(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->view->render($name);
    }

    public function testAFileWithoutAFunctionIsRefused(): void
    {
        $this->expectException(LogicException::class);

        $this->view->render('answer');
    }

    public function testAFailingTemplateClosesItsBuffer(): void
    {
        $level = ob_get_level();
        try {
            $this->view->render('broken');
            self::fail('The exception should reach the caller');
        } catch (RuntimeException $e) {
            self::assertSame('The template failed', $e->getMessage());
        }
        self::assertSame($level, ob_get_level());
    }
}
