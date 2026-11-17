<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use PHPUnit\Framework\TestCase;
use Shelf\Book;
use DateTimeImmutable;
use Shelf\Library;
use Shelf\Member;
use Shelf\Role;
use Shelf\Tests\InMemoryBookRepository;
use Shelf\Tests\InMemoryLoanRepository;
use Shelf\Tests\InMemoryMemberRepository;
use Shelf\Web\App;
use Shelf\Web\Covers;
use Shelf\Web\Request;
use Shelf\Web\Templates;
use Shelf\Web\Upload;

/** The web pages, tested without a server: a Request goes in, a Response comes out. */
final class AppTest extends TestCase
{
    private App $app;
    private Library $library;
    private ArraySession $session;
    private string $covers;

    protected function setUp(): void
    {
        $this->library = new Library(
            new InMemoryBookRepository([new Book('Kindred', 'Octavia E. Butler', 1979)]),
            new InMemoryMemberRepository([new Member('Amira', Role::Reader)]),
            new InMemoryLoanRepository(),
        );
        $this->session = new ArraySession(['csrf' => 'the-token']);
        $this->covers = sys_get_temp_dir() . '/shelf-covers-' . bin2hex(random_bytes(4));
        $this->app = new App($this->library, new Templates(__DIR__ . '/../../templates'), $this->session, new Covers($this->covers));
    }

    protected function tearDown(): void
    {
        foreach (glob("$this->covers/*") ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->covers)) {
            rmdir($this->covers);
        }
    }

    public function testTheListShowsTheBooksEscaped(): void
    {
        $this->library->add(['Tom & Jerry', 'Someone', '1940']);

        $response = $this->app->handle(new Request('GET', '/'));

        self::assertSame(200, $response->status);
        self::assertStringContainsString("<h2><a href=\"/book?title=Kindred\">Kindred</a></h2>\n            <p>Octavia E. Butler, 1979</p>", $response->body);
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

    public function testABookCanComeWithACover(): void
    {
        $form = ['title' => 'Sula', 'author' => 'Toni Morrison', 'year' => '1973', '_token' => 'the-token'];
        $this->app->handle(new Request('POST', '/add', form: $form, files: ['cover' => new Upload(UPLOAD_ERR_OK, __DIR__ . '/cover.png')]));

        $list = $this->app->handle(new Request('GET', '/'))->body;
        $cover = $this->app->handle(new Request('GET', '/cover', query: ['title' => 'Sula']));

        self::assertStringContainsString('<img class="cover" src="/cover?title=Sula" alt="">', $list);
        self::assertSame(200, $cover->status);
        self::assertSame(['Content-Type' => 'image/png', 'X-Content-Type-Options' => 'nosniff'], $cover->headers);
        self::assertStringEqualsFile(__DIR__ . '/cover.png', $cover->body);
    }

    public function testAWrongCoverRefusesTheWholeBook(): void
    {
        $form = ['title' => 'Sula', 'author' => 'Toni Morrison', 'year' => '1973', '_token' => 'the-token'];
        $response = $this->app->handle(new Request('POST', '/add', form: $form, files: ['cover' => new Upload(UPLOAD_ERR_OK, __DIR__ . '/not-a-cover.png')]));

        self::assertSame(422, $response->status);
        self::assertStringContainsString('A cover must be a PNG or JPEG image.', $response->body);
        self::assertCount(1, $this->library->books());
    }

    public function testABookWithoutACoverHasNone(): void
    {
        self::assertSame(404, $this->app->handle(new Request('GET', '/cover', query: ['title' => 'Kindred']))->status);
    }

    public function testABookHasItsOwnPage(): void
    {
        $response = $this->app->handle(new Request('GET', '/book', query: ['title' => 'kindred']));

        self::assertSame(200, $response->status);
        self::assertStringContainsString('<h1>Kindred</h1>', $response->body);
        self::assertStringContainsString('<input name="copies" value="1"', $response->body);
    }

    public function testAnUnknownBookHasNoPage(): void
    {
        self::assertSame(404, $this->app->handle(new Request('GET', '/book', query: ['title' => 'Dune']))->status);
    }

    public function testTheCopiesAreSavedAndTheBookPageSaysSo(): void
    {
        $response = $this->app->handle(new Request('POST', '/copies', form: ['title' => 'Kindred', 'copies' => '3', '_token' => 'the-token']));

        self::assertSame(303, $response->status);
        self::assertSame(['Location' => '/book?title=Kindred'], $response->headers);
        self::assertSame(3, $this->library->find('Kindred')->copies);
        self::assertStringContainsString('Saved: the library has 3 copies.', $this->app->handle(new Request('GET', '/book', query: ['title' => 'Kindred']))->body);
    }

    public function testWrongCopiesComeBackWith422(): void
    {
        $response = $this->app->handle(new Request('POST', '/copies', form: ['title' => 'Kindred', 'copies' => '-1', '_token' => 'the-token']));

        self::assertSame(422, $response->status);
        self::assertStringContainsString('<input name="copies" value="-1"', $response->body);
        self::assertSame(1, $this->library->find('Kindred')->copies);
    }

    public function testARemovedBookLosesItsCover(): void
    {
        $form = ['title' => 'Sula', 'author' => 'Toni Morrison', 'year' => '1973', '_token' => 'the-token'];
        $this->app->handle(new Request('POST', '/add', form: $form, files: ['cover' => new Upload(UPLOAD_ERR_OK, __DIR__ . '/cover.png')]));

        $response = $this->app->handle(new Request('POST', '/remove', form: ['title' => 'Sula', '_token' => 'the-token']));

        self::assertSame(303, $response->status);
        self::assertSame(['Kindred'], array_column($this->library->books(), 'title'));
        self::assertSame(404, $this->app->handle(new Request('GET', '/cover', query: ['title' => 'Sula']))->status);
        self::assertSame('Removed "Sula".', $this->session->values['flash'] ?? null);
    }

    public function testABookOnLoanStays(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $response = $this->app->handle(new Request('POST', '/remove', form: ['title' => 'Kindred', '_token' => 'the-token']));

        self::assertSame(422, $response->status);
        self::assertStringContainsString('&quot;Kindred&quot; is on loan, so it can&#039;t be removed.', $response->body);
        self::assertCount(1, $this->library->books());
    }

    public function testRemovingNeedsAPostWithTheToken(): void
    {
        self::assertSame(405, $this->app->handle(new Request('GET', '/remove', query: ['title' => 'Kindred']))->status);
        self::assertSame(403, $this->app->handle(new Request('POST', '/remove', form: ['title' => 'Kindred']))->status);
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
