<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DateTimeImmutable;
use Shelf\BookRepository;
use Shelf\EventSubscriber\SecurityHeaders;
use Shelf\Library;

/**
 * Shelf's pages (chapter 77), as a browser uses them: each test sends requests to the kernel, follows
 * redirects, fills in forms and reads the HTML. What chapters 48 to 64 tested with App, the Symfony way.
 */
final class PagesTest extends ShelfWebTestCase
{
    public function testTheListShowsEveryBook(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'Books');
        self::assertSelectorTextSame('.book h2', 'Kindred');
        self::assertSelectorTextSame('.count', '1 book');
        self::assertSelectorNotExists('a.button', 'A visitor sees no "Add a book"');
    }

    /** Chapter 78: every page has the headers of chapter 64; from chapter 73 to 77 they were missing. */
    public function testEveryPageSendsItsSecurityHeaders(): void
    {
        foreach (['/', '/login', '/book?title=Sula'] as $path) {
            $this->client->request('GET', $path);

            self::assertResponseHeaderSame('Content-Security-Policy', SecurityHeaders::FOR_PAGES['Content-Security-Policy'], $path);
            self::assertResponseHeaderSame('Referrer-Policy', 'same-origin', $path);
            self::assertResponseHeaderSame('X-Content-Type-Options', 'nosniff', $path);
        }
    }

    public function testAnUnknownBookIsNotFound(): void
    {
        $this->client->request('GET', '/book?title=Sula');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextSame('h1', 'Not found');
    }

    public function testAVisitorIsSentToTheLogin(): void
    {
        $this->client->request('GET', '/add');

        self::assertResponseRedirects('https://localhost/login');
    }

    public function testAMemberLogsInWithTheForm(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Log in', ['name' => 'Amira', 'password' => self::AMIRA]);

        self::assertResponseRedirects('https://localhost/');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Welcome, Amira.');
        self::assertSelectorTextContains('.who', 'Amira');
    }

    public function testAWrongPasswordKeepsOnlyTheName(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Log in', ['name' => 'Amira', 'password' => 'tea kettle']);
        $this->client->followRedirect();

        self::assertSelectorTextSame('.error', 'Wrong name or password.');
        self::assertInputValueSame('name', 'Amira');
        self::assertInputValueSame('password', '');
        self::assertSelectorNotExists('.who', 'Nobody is logged in');
    }

    public function testALoginWithoutItsTokenIsRefused(): void
    {
        $this->client->request('POST', '/login', ['name' => 'Amira', 'password' => self::AMIRA]);
        $this->client->followRedirect();

        self::assertSelectorNotExists('.who', 'A form from another site logs nobody in');
    }

    public function testAReaderMayNotAddBooks(): void
    {
        $this->logIn('Amira');
        $this->client->request('GET', '/add');

        self::assertResponseStatusCodeSame(403);
        self::assertSelectorTextSame('h1', 'Not allowed');
    }

    public function testStaffAddABook(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/add');
        $this->client->submitForm('Add the book', ['title' => 'Sula', 'author' => 'Toni Morrison', 'year' => '1973']);

        self::assertResponseRedirects('/', 303);
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Added "Sula".');
        self::assertSame(1973, self::service(BookRepository::class)->find('Sula')?->year, 'It is in the database');
    }

    public function testTheFormNamesEveryMistake(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/add');
        $crawler = $this->client->submitForm('Add the book', ['title' => '', 'author' => '', 'year' => '']);

        self::assertResponseStatusCodeSame(422);
        self::assertCount(3, $crawler->filter('.error'), 'Title, author and year, each next to its field');
        self::assertNull(self::service(BookRepository::class)->find(''));
    }

    public function testAFormWithoutItsTokenChangesNothing(): void
    {
        $this->logIn('Jonas');
        $this->client->request('POST', '/remove', ['title' => 'Kindred']);

        self::assertResponseStatusCodeSame(403);
        self::assertNotNull(self::service(BookRepository::class)->find('Kindred'));
    }

    public function testAMemberSeesTheirOwnLoans(): void
    {
        self::service(Library::class)->lend('Kindred', 'Amira', new DateTimeImmutable('2026-11-17'));
        $this->logIn('Amira');
        $this->client->request('GET', '/loans');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.loans li', 'Kindred, due back on Tuesday, 1 December 2026');
    }

    public function testLoggingOutEndsTheSession(): void
    {
        $this->logIn('Amira');
        $this->client->request('GET', '/');
        $this->client->submitForm('Log out');
        $this->client->followRedirect();

        self::assertSelectorTextSame('.flash', 'You are logged out.');
        $this->client->request('GET', '/loans');
        self::assertResponseRedirects('https://localhost/login');
    }
}
