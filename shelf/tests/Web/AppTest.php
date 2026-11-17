<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use PHPUnit\Framework\TestCase;
use Shelf\Accounts;
use Shelf\Book;
use DateTimeImmutable;
use Shelf\Library;
use Shelf\Member;
use Shelf\Role;
use Shelf\Tests\ArrayLogger;
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
    private ArrayLogger $log;

    protected function setUp(): void
    {
        $books = new InMemoryBookRepository([new Book('Kindred', 'Octavia E. Butler', 1979)]);
        $members = new InMemoryMemberRepository([new Member('Amira', Role::Reader), new Member('Jonas', Role::Staff)]);
        $this->library = new Library($books, $members, new InMemoryLoanRepository($books));
        $accounts = new Accounts($members, ['cost' => 4]);
        $accounts->setPassword('Amira', 'tea kettle under the lemon tree');
        $this->session = new ArraySession(['csrf' => 'the-token', 'member' => 'Jonas']); // staff: may change books (chapter 63)
        $this->covers = sys_get_temp_dir() . '/shelf-covers-' . bin2hex(random_bytes(4));
        $this->log = new ArrayLogger();
        $this->app = new App($this->library, $accounts, new Templates(__DIR__ . '/../../templates'), $this->session, new Covers($this->covers), $this->log);
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

    public function testTheRightPasswordLogsInWithANewSessionId(): void
    {
        $this->session->pull('member');
        $response = $this->app->handle(new Request('POST', '/login', form: ['name' => 'amira', 'password' => 'tea kettle under the lemon tree', '_token' => 'the-token']));

        self::assertSame([303, ['Location' => '/']], [$response->status, $response->headers]);
        self::assertSame('Amira', $this->session->values['member'] ?? null);
        self::assertSame(1, $this->session->regenerated);
    }

    public function testAWrongPasswordComesBackWithoutIt(): void
    {
        $this->session->pull('member');
        $response = $this->app->handle(new Request('POST', '/login', form: ['name' => 'Amira', 'password' => 'tea kettle', '_token' => 'the-token']));

        self::assertSame(422, $response->status);
        self::assertStringContainsString('<p class="error" role="alert">Wrong name or password.</p>', $response->body);
        self::assertStringContainsString('<input name="name" value="Amira"', $response->body);
        self::assertStringNotContainsString('tea kettle', $response->body);
        self::assertArrayNotHasKey('member', $this->session->values);
        self::assertSame(0, $this->session->regenerated);
        self::assertSame(['warning Failed login for Amira'], $this->log->lines, 'Logged, without the password');
    }

    public function testThePasswordIsNotTrimmed(): void
    {
        $response = $this->app->handle(new Request('POST', '/login', form: ['name' => 'Amira', 'password' => ' tea kettle under the lemon tree ', '_token' => 'the-token']));

        self::assertSame(422, $response->status);
    }

    public function testTheBarShowsWhoIsLoggedIn(): void
    {
        $this->session->pull('member');
        self::assertStringContainsString('<a href="/login">Log in</a>', $this->app->handle(new Request('GET', '/'))->body);

        $this->session->set('member', 'Amira');
        $page = $this->app->handle(new Request('GET', '/'))->body;

        self::assertStringContainsString('<span>Amira</span> <button class="link">Log out</button>', $page);
        self::assertStringNotContainsString('href="/login"', $page);
    }

    public function testLoggingOutForgetsTheMember(): void
    {
        $this->session->set('member', 'Amira');

        $response = $this->app->handle(new Request('POST', '/logout', form: ['_token' => 'the-token']));

        self::assertSame(303, $response->status);
        self::assertArrayNotHasKey('member', $this->session->values);
        self::assertSame(1, $this->session->regenerated);
    }

    public function testLoggingOutNeedsAPost(): void
    {
        self::assertSame(405, $this->app->handle(new Request('GET', '/logout'))->status);
    }

    public function testAVisitorIsSentToTheLogin(): void
    {
        $this->session->pull('member');

        foreach ([new Request('GET', '/add'), new Request('GET', '/loans'), new Request('POST', '/remove', form: ['title' => 'Kindred', '_token' => 'the-token'])] as $request) {
            $response = $this->app->handle($request);
            self::assertSame([303, ['Location' => '/login']], [$response->status, $response->headers], $request->path);
        }
        self::assertCount(1, $this->library->books());
        self::assertStringContainsString('<p class="flash" role="status">Log in first.</p>', $this->app->handle(new Request('GET', '/login'))->body);
    }

    public function testAReaderMayNotChangeBooks(): void
    {
        $this->session->set('member', 'Amira');

        $add = $this->app->handle(new Request('POST', '/add', form: ['title' => 'Sula', 'author' => 'Toni Morrison', 'year' => '1973', '_token' => 'the-token']));
        $remove = $this->app->handle(new Request('POST', '/remove', form: ['title' => 'Kindred', '_token' => 'the-token']));

        self::assertSame([403, 403], [$add->status, $remove->status]);
        self::assertSame(['warning Amira was refused /add', 'warning Amira was refused /remove'], $this->log->lines);
        self::assertStringContainsString('Only the library\'s staff may change books.', $add->body);
        self::assertCount(1, $this->library->books());
    }

    public function testOnlyStaffSeeTheFormsToChangeBooks(): void
    {
        $staff = $this->app->handle(new Request('GET', '/book', query: ['title' => 'Kindred']))->body;
        $this->session->set('member', 'Amira');
        $reader = $this->app->handle(new Request('GET', '/book', query: ['title' => 'Kindred']))->body;

        self::assertStringContainsString('action="/remove"', $staff);
        self::assertStringContainsString('<a class="button" href="/add">Add a book</a>', $staff);
        self::assertStringNotContainsString('action="/remove"', $reader);
        self::assertStringNotContainsString('href="/add"', $reader);
    }

    public function testAMemberSeesTheirOwnLoans(): void
    {
        $this->session->set('member', 'Amira');
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $page = $this->app->handle(new Request('GET', '/loans'))->body;

        self::assertStringContainsString('<a href="/book?title=Kindred">Kindred</a>, due back on Monday, 16 March 2026', $page);
    }

    public function testARemovedMemberIsLoggedInNoMore(): void
    {
        $this->session->set('member', 'Kenji'); // not a member (any more)

        self::assertSame(303, $this->app->handle(new Request('GET', '/loans'))->status);
    }

    public function testEveryPageCarriesTheSecurityHeaders(): void
    {
        foreach (['/', '/login', '/nowhere'] as $path) {
            $headers = $this->app->handle(new Request('GET', $path))->headers;

            self::assertStringContainsString("script-src 'none'", $headers['Content-Security-Policy'] ?? '', $path);
            self::assertSame('nosniff', $headers['X-Content-Type-Options'] ?? null, $path);
        }
    }
}
