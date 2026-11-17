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
        self::assertStringContainsString('<h2><a href="/book?title=Tom+%26+Jerry">Tom &amp; Jerry</a></h2>', $html);
        self::assertStringContainsString('<p class="count">1 book</p>', $html);
    }

    public function testAnEmptyShelfSaysWhatToDo(): void
    {
        $html = $this->templates->render('list', [null, []]);

        self::assertStringContainsString('No books yet. <a href="/add">Add the first one.</a>', $html);
        self::assertStringNotContainsString('<ul', $html);
    }

    public function testABookPageEscapesWhatItShows(): void
    {
        $html = $this->templates->render('book', [
            'book' => new Book('Tom & Jerry', 'Someone', 1940),
            'hasCover' => false,
            'flash' => null,
            'copies' => '"><script>',
            'error' => 'Copies must be a whole number from 0 to 999.',
            'token' => 'the-token',
            'mayChange' => true,
        ]);

        self::assertStringContainsString('<h1>Tom &amp; Jerry</h1>', $html);
        self::assertStringContainsString('<input name="copies" value="&quot;&gt;&lt;script&gt;"', $html);
        self::assertSame(2, substr_count($html, '<input type="hidden" name="title" value="Tom &amp; Jerry">'));
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
