<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DateTimeImmutable;
use Shelf\Accounts;
use Shelf\BookRepository;
use Shelf\Demo;
use Shelf\Library;
use Shelf\Loan;
use Shelf\MemberRepository;

/**
 * Shelf as a demo (chapter 88): SHELF_DEMO=1, set before the kernel starts, so its container reads it.
 * The members of ShelfWebTestCase (Amira, Jonas) are the shared accounts.
 */
final class DemoTest extends ShelfWebTestCase
{
    protected function setUp(): void
    {
        $_SERVER['SHELF_DEMO'] = $_ENV['SHELF_DEMO'] = '1';
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $_SERVER['SHELF_DEMO'] = $_ENV['SHELF_DEMO'] = '0';
        parent::tearDown();
    }

    public function testTheLoginPageOffersTheSharedAccounts(): void
    {
        $notices = $this->client->request('GET', '/login')->filter('main .notice');

        self::assertStringContainsString('Every night at 03:00 UTC', $notices->first()->text(), 'On every page');
        self::assertStringEndsWith('Both have the password: ' . Demo::PASSWORD, $notices->last()->text());
        self::assertSelectorNotExists('a[href="/signup"]');
        self::assertSelectorNotExists('a[href="/forgot"]');
    }

    public function testNothingThatSendsEmailsWorks(): void
    {
        foreach (['/signup', '/de/forgot', '/ar/reset'] as $path) {
            $this->client->request('GET', $path);
            self::assertResponseStatusCodeSame(403, $path);
        }
        self::assertSelectorTextContains('h1', 'غير متاح في النسخة التجريبية');
    }

    public function testTheSharedAccountsStayAsTheyAre(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/members');

        $this->client->submitForm('Block Amira');

        self::assertResponseStatusCodeSame(403);
        self::assertSelectorTextContains('h1', 'Not in the demo');
        self::assertFalse(self::service(MemberRepository::class)->find('Amira')?->blocked);
    }

    public function testTheDemoTakesNoCovers(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/add');

        $this->client->submitForm('Add the book', ['title' => 'Sula', 'author' => 'Toni Morrison', 'year' => '1973', 'cover' => __DIR__ . '/cover.png']);

        self::assertSelectorTextContains('.error', 'The demo takes no covers');
        self::assertNull(self::service(BookRepository::class)->find('Sula'));
    }

    public function testTheResetPutsTheLibraryBack(): void
    {
        self::service(Library::class)->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $now = self::service(Demo::class)->reset(new DateTimeImmutable('2026-03-16'));

        self::assertSame(['books' => 57, 'members' => 4], $now);
        $library = self::service(Library::class);
        self::assertSame(['Things Fall Apart'], array_map(fn(Loan $loan): string => $loan->book->title, $library->loansOf('Amira')), 'The demo\'s loan, not the test\'s');
        self::assertCount(1, $library->overdueLoans(new DateTimeImmutable('2026-03-16')), 'Kenji\'s');
        self::assertSame('Kitchen', $library->holdsOf($library->findMember('Amira') ?? self::fail('No Amira'))[0]['hold']->book->title);
        self::assertNotNull(self::service(Accounts::class)->authenticate('Jonas', Demo::PASSWORD));
        self::assertNull(self::service(Accounts::class)->authenticate('Jonas', self::JONAS), 'The old password is gone');
    }
}
