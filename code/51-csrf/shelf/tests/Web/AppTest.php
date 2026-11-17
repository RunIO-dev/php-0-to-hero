<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\Library;
use Shelf\Tests\InMemoryBookRepository;
use Shelf\Web\App;
use Shelf\Web\Request;
use Shelf\Web\Templates;

/** The web pages, tested without a server: a Request goes in, a Response comes out. */
final class AppTest extends TestCase
{
    private App $app;
    private Library $library;
    private ArraySession $session;

    protected function setUp(): void
    {
        $this->library = new Library(new InMemoryBookRepository([new Book('Kindred', 'Octavia E. Butler', 1979)]), []);
        $this->session = new ArraySession(['csrf' => 'the-token']);
        $this->app = new App($this->library, new Templates(__DIR__ . '/../../templates'), $this->session);
    }

    public function testTheListShowsTheBooksEscaped(): void
    {
        $this->library->add(['Tom & Jerry', 'Someone', '1940']);

        $response = $this->app->handle(new Request('GET', '/'));

        self::assertSame(200, $response->status);
        self::assertStringContainsString("<h2>Kindred</h2>\n            <p>Octavia E. Butler, 1979</p>", $response->body);
        self::assertStringContainsString('Tom &amp; Jerry', $response->body);
    }

    public function testAGoodBookIsAddedAndRedirects(): void
    {
        $response = $this->app->handle(new Request('POST', '/add', form: ['title' => 'Sula', 'author' => 'Toni Morrison', 'year' => '1973', '_token' => 'the-token']));

        self::assertSame(303, $response->status);
        self::assertSame(['Location' => '/'], $response->headers);
        self::assertCount(2, $this->library->books());
        self::assertSame('Added "Sula".', $this->session->values['flash'] ?? null);
    }

    public function testTheMessageIsShownOnce(): void
    {
        $this->session->set('flash', 'Added "Tom & Jerry".');

        $first = $this->app->handle(new Request('GET', '/'))->body;
        $second = $this->app->handle(new Request('GET', '/'))->body;

        self::assertStringContainsString('<p class="flash" role="status">Added &quot;Tom &amp; Jerry&quot;.</p>', $first);
        self::assertStringNotContainsString('flash', $second);
    }

    public function testAWrongBookComesBackWith422(): void
    {
        $response = $this->app->handle(new Request('POST', '/add', form: ['title' => '<b>', 'author' => '', 'year' => '1973', '_token' => 'the-token']));

        self::assertSame(422, $response->status);
        self::assertStringContainsString('value="&lt;b&gt;"', $response->body);
        self::assertCount(1, $this->library->books());
    }

    public function testTheFormCarriesTheToken(): void
    {
        $body = $this->app->handle(new Request('GET', '/add'))->body;

        self::assertStringContainsString('<input type="hidden" name="_token" value="the-token">', $body);
    }

    public function testAPostWithoutTheRightTokenIs403AndChangesNothing(): void
    {
        foreach (['', 'guessed'] as $token) {
            $response = $this->app->handle(new Request('POST', '/add', form: ['title' => 'Sula', 'author' => 'Toni Morrison', 'year' => '1973', '_token' => $token]));

            self::assertSame(403, $response->status);
            self::assertStringContainsString('This form has expired', $response->body);
        }
        self::assertCount(1, $this->library->books());
    }

    public function testAnUnknownPathIs404(): void
    {
        self::assertSame(404, $this->app->handle(new Request('GET', '/nothing'))->status);
    }

    public function testAKnownPathWithTheWrongMethodIs405(): void
    {
        $response = $this->app->handle(new Request('DELETE', '/add'));

        self::assertSame(405, $response->status);
        self::assertSame(['Allow' => 'GET, POST'], $response->headers);
    }
}
