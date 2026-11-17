<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use Error;
use LogicException;
use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\Web\Templates;

final class TemplatesTest extends TestCase
{
    private Templates $templates;

    protected function setUp(): void
    {
        $this->templates = new Templates(__DIR__ . '/../../templates');
    }

    public function testAPageIsTheTemplateInsideTheLayout(): void
    {
        $html = $this->templates->page('Tom & Jerry', 'list', [null, [], new Book('Tom & Jerry', 'Someone', 1940)]);

        self::assertStringContainsString('<title>Tom &amp; Jerry</title>', $html);
        self::assertStringContainsString('<h2>Tom &amp; Jerry</h2>', $html);
        self::assertStringContainsString('1 books', $html);
    }

    public function testAnEmptyShelfSaysWhatToDo(): void
    {
        $html = $this->templates->render('list', [null, []]);

        self::assertStringContainsString('No books yet. <a href="/add">Add the first one.</a>', $html);
        self::assertStringNotContainsString('<ul', $html);
    }

    public function testAWrongNameInTheDataStops(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Unknown named parameter $titel');

        $this->templates->render('add', ['titel' => '', 'author' => '', 'year' => '', 'isbn' => '', 'error' => null, 'token' => '']);
    }

    public function testOnlyTemplateNamesAreLoaded(): void
    {
        $this->expectException(LogicException::class);

        $this->templates->render('../src/functions', []);
    }
}
