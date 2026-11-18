<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DateTimeImmutable;
use Shelf\BookRepository;
use Shelf\Library;
use Shelf\MemberRepository;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

/**
 * The lending desk and the members (chapter 83), as the staff use them in the browser, and "Extend"
 * on a reader's own loans. Jonas is staff, Amira a reader; Kindred has two copies.
 */
final class DeskTest extends ShelfWebTestCase
{
    use ClockSensitiveTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::mockTime('2026-03-02 10:00');
    }

    public function testTheStaffLendAndTakeBack(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/desk');
        self::assertSelectorTextContains('.empty', 'Every book is on the shelf.');

        $this->client->submitForm('Lend', ['title' => 'kindred', 'member' => 'amira']);
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Lent: Kindred to Amira, due back on Monday, March 16, 2026.');
        self::assertSelectorTextSame('tbody td:nth-child(2)', 'Amira');
        self::assertSame(1, self::service(BookRepository::class)->find('Kindred')?->copies);

        $this->client->submitForm('Back');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', '"Kindred" is back from Amira.');
        self::assertSame(2, self::service(BookRepository::class)->find('Kindred')?->copies);
    }

    public function testABooksPageLeadsToTheDesk(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/book?title=Kindred');

        $this->client->clickLink('Lend this book at the desk');

        self::assertInputValueSame('title', 'Kindred');
    }

    public function testTheDeskSaysWhyItCantLend(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/desk');

        $this->client->submitForm('Lend', ['title' => 'Kindred', 'member' => 'Nobody']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'Nobody');
        self::assertInputValueSame('title', 'Kindred', 'What was typed stays');
    }

    public function testALoanIsExtendedAtTheDesk(): void
    {
        self::service(Library::class)->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->logIn('Jonas');
        $this->client->request('GET', '/desk');

        $this->client->submitForm('Extend');
        $this->client->followRedirect();

        self::assertSelectorTextSame('.flash', '"Kindred" is now due back on Monday, March 30, 2026.');
        self::assertSelectorTextContains('tbody td:nth-child(3)', 'extended 1×');
    }

    public function testAReaderExtendsTheirOwnLoanOnly(): void
    {
        $library = self::service(Library::class);
        $mine = $library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $theirs = $library->lend('Kindred', 'Jonas', new DateTimeImmutable('2026-03-02'));
        $this->logIn('Amira');
        $this->client->request('GET', '/loans');

        $this->client->submitForm('Extend');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', '"Kindred" is now due back on Monday, March 30, 2026.');
        self::assertSame(1, $library->loan($mine->id())->renewals);

        $token = $this->client->getCrawler()->filter('form input[name="_token"]')->attr('value');
        $this->client->request('POST', '/renew', ['loan' => $theirs->id(), '_token' => $token]);
        self::assertResponseStatusCodeSame(404, "Jonas's loan is no business of Amira's");
        $this->client->request('POST', '/renew', ['loan' => $theirs->id(), 'from' => 'desk', '_token' => $token]);
        self::assertResponseStatusCodeSame(404, 'Saying "desk" changes nothing for a reader');
    }

    public function testAnOverdueLoanCantBeExtended(): void
    {
        $loan = self::service(Library::class)->lend('Kindred', 'Amira', new DateTimeImmutable('2026-02-01'));
        $this->logIn('Amira');
        $this->client->request('GET', '/loans');
        self::assertSelectorNotExists('button:contains("Extend")', 'No button for what the rules refuse');

        $token = $this->client->getCrawler()->filter('form input[name="_token"]')->attr('value');
        $this->client->request('POST', '/renew', ['loan' => $loan->id(), '_token' => $token]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextSame('.error', '"Kindred" is overdue: please bring it back to the library.');
    }

    public function testTheStaffBlockAMember(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/members');
        self::assertSelectorTextSame('tbody tr:nth-child(2) td:last-child', 'You');

        $this->client->submitForm('Block Amira');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', "Amira is blocked: they can't borrow until you unblock them.");
        self::assertSelectorTextContains('tbody tr:first-child .badge', 'Blocked');

        $this->client->request('GET', '/desk');
        $this->client->submitForm('Lend', ['title' => 'Kindred', 'member' => 'Amira']);
        self::assertSelectorTextSame('.error', "Amira is blocked, and can't borrow books.");
    }

    public function testTheStaffChangeARole(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/members');

        $this->client->submitForm('Make staff Amira');
        $this->client->followRedirect();

        self::assertSelectorTextSame('.flash', 'Amira is a member of staff now.');
        self::assertSame('staff', self::service(MemberRepository::class)->find('Amira')?->role->value);
    }

    public function testNobodyChangesThemselves(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/members');
        $token = $this->client->getCrawler()->filter('form input[name="_token"]')->attr('value');

        $this->client->request('POST', '/members/role', ['member' => 'Jonas', 'role' => 'reader', '_token' => $token]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', "You can't change your own account here");
    }

    public function testTheDeskIsForStaff(): void
    {
        $this->logIn('Amira');

        foreach (['/desk', '/members'] as $path) {
            $this->client->request('GET', $path);
            self::assertResponseStatusCodeSame(403, $path);
        }
        self::assertSelectorNotExists('nav a[href="/desk"]');
    }
}
