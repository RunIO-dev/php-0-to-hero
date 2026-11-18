<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DateTimeImmutable;
use Shelf\Book;
use Shelf\BookRepository;
use Shelf\Library;
use Shelf\Member;
use Shelf\MemberRepository;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

/**
 * Reservations (chapter 84), in the browser: a reader reserves a book whose copies are all out, the
 * desk sets a returned copy aside for them, and lends it with one button. Kindred has two copies;
 * in most tests Jonas has both.
 */
final class HoldsTest extends ShelfWebTestCase
{
    use ClockSensitiveTrait;

    private Library $library;

    protected function setUp(): void
    {
        parent::setUp();
        self::mockTime('2026-03-02 10:00');
        $this->library = self::service(Library::class);
    }

    private function bothCopiesOut(): void
    {
        $this->library->lend('Kindred', 'Jonas', new DateTimeImmutable('2026-03-02'));
        $this->library->lend('Kindred', 'Jonas', new DateTimeImmutable('2026-03-02'));
    }

    private function amira(): Member
    {
        return self::service(MemberRepository::class)->find('Amira') ?? self::fail('No Amira');
    }

    public function testAReaderReservesABookWhoseCopiesAreOut(): void
    {
        $this->bothCopiesOut();
        $this->logIn('Amira');
        $this->client->request('GET', '/book?title=Kindred');

        $this->client->submitForm('Reserve');
        $this->client->followRedirect();

        self::assertSelectorTextSame('.flash', 'You reserved "Kindred". The next copy that comes back is kept for you.');
        self::assertSelectorTextContains('#hold-head + p', 'You are number 1 in the queue.');
        self::assertSelectorNotExists('button:contains("Reserve")', 'Once is enough');
    }

    public function testABookOnTheShelfIsBorrowedNotReserved(): void
    {
        $this->logIn('Amira');
        $this->client->request('GET', '/book?title=Kindred');
        self::assertSelectorNotExists('button:contains("Reserve")');

        $token = $this->client->getCrawler()->filter('input[name="_token"]')->attr('value'); // the logout form's
        $this->client->request('POST', '/reserve', ['title' => 'Kindred', '_token' => $token]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextSame('.error', '"Kindred" is on the shelf: borrow it at the desk, no need to reserve it.');
    }

    public function testAReturnedCopyIsSetAsideAndLentAtTheDesk(): void
    {
        $this->bothCopiesOut();
        $this->library->reserve('Kindred', $this->amira(), new DateTimeImmutable('2026-03-02'));
        $this->logIn('Jonas');
        $this->client->request('GET', '/desk');

        $this->client->submitForm('Back');
        $crawler = $this->client->followRedirect();

        self::assertSelectorTextSame('.flash', '"Kindred" is back from Jonas. Please set it aside for Amira, who reserved it.');
        self::assertSame(0, self::service(BookRepository::class)->find('Kindred')?->copies, 'Set aside, not on the shelf');
        self::assertSame('Amira', $crawler->filter('#ready + .table-wrap tbody td:nth-child(2)')->text());

        $this->client->submit($crawler->filter('#ready + .table-wrap')->selectButton('Lend')->form());
        $this->client->followRedirect();

        self::assertSelectorTextSame('.flash', 'Lent: Kindred to Amira, due back on Monday, March 16, 2026.');
        self::assertSelectorNotExists('#ready');
        self::assertSame([], $this->library->holdsOf($this->amira()), 'Collected');
    }

    public function testNoExtendingWhileSomeoneWaits(): void
    {
        $this->bothCopiesOut();
        $this->library->reserve('Kindred', $this->amira(), new DateTimeImmutable('2026-03-02'));
        $this->logIn('Jonas');
        $this->client->request('GET', '/desk');
        self::assertSelectorNotExists('button:contains("Extend")');

        $token = $this->client->getCrawler()->filter('input[name="_token"]')->attr('value');
        $loan = $this->library->currentLoans()[0];
        $this->client->request('POST', '/renew', ['loan' => $loan->id(), 'from' => 'desk', '_token' => $token]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextSame('.error', '"Kindred" is reserved by another member, so it can\'t be extended: please bring it back by Monday, March 30, 2026.');
    }

    public function testAReaderCancelsTheirOwnReservationOnly(): void
    {
        $this->bothCopiesOut();
        self::service(BookRepository::class)->add(new Book('Sula', 'Toni Morrison', 1973, 1));
        $this->library->lend('Sula', 'Amira', new DateTimeImmutable('2026-03-02'));
        $jonass = $this->library->reserve('Sula', self::service(MemberRepository::class)->find('Jonas') ?? self::fail('No Jonas'), new DateTimeImmutable('2026-03-02'));
        $this->library->reserve('Kindred', $this->amira(), new DateTimeImmutable('2026-03-02'));
        $this->logIn('Amira');
        $this->client->request('GET', '/loans');
        self::assertSelectorTextContains('.section-head + .table-wrap .badge', 'Number 1 in the queue');

        $token = $this->client->getCrawler()->filter('input[name="_token"]')->attr('value');
        $this->client->request('POST', '/reservations/cancel', ['hold' => $jonass->id(), '_token' => $token]);
        self::assertResponseStatusCodeSame(404, "Jonas's reservation is no business of Amira's");

        $this->client->request('GET', '/loans');
        $this->client->submitForm('Cancel');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Your reservation of "Kindred" is cancelled.');
        self::assertSame([], $this->library->holdsOf($this->amira()));
    }
}
