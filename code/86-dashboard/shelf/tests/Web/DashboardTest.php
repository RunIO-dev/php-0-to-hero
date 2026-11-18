<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use Shelf\Library;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

/**
 * The dashboard and the loan history (chapter 86), in the browser. Amira borrows Kindred on 2 March;
 * on 20 March it is four days overdue.
 */
final class DashboardTest extends ShelfWebTestCase
{
    use ClockSensitiveTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::mockTime('2026-03-02 10:00');
        self::service(Library::class)->lend('Kindred', 'Amira', new \DateTimeImmutable('2026-03-02'));
        self::mockTime('2026-03-20 10:00');
    }

    public function testTheDashboardShowsWhatIsOverdueAndTheNumbers(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/dashboard');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.stats .warn', '1 overdue');
        self::assertSelectorTextSame('#overdue + .table-wrap tbody td:nth-child(2)', 'Amira');
        self::assertSelectorTextContains('#overdue + .table-wrap tbody', '4 days overdue');
        self::assertSelectorTextContains('.tally', 'Kindred 1×');
        self::assertSelectorTextContains('.bars', 'Mar 2026');
        self::assertSelectorTextContains('.report', 'No book came back in the last 12 months.');
    }

    public function testTheHistoryNarrowsToABookOrAMember(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/dashboard');

        $this->client->clickLink('Loan history');
        self::assertSelectorTextSame('.count[role="status"]', '1 loan');
        self::assertSelectorTextContains('tbody', 'out, 4 days late');

        $this->client->submitForm('Show', ['member' => 'jonas'], 'GET'); // submitForm() sends POST unless told
        self::assertSelectorTextSame('.count[role="status"]', '0 loans by jonas');
        self::assertSelectorTextContains('.empty', 'No loans to show.');

        $this->client->request('GET', '/history?title=kindred');
        self::assertSelectorTextSame('.count[role="status"]', '1 loan of “kindred”');
    }

    public function testAPageAfterTheLastIsNotFound(): void
    {
        $this->logIn('Jonas');

        $this->client->request('GET', '/history?page=2');
        self::assertResponseStatusCodeSame(404);

        $this->client->request('GET', '/history?page=0');
        self::assertResponseStatusCodeSame(400);
    }

    public function testTheDashboardIsForStaff(): void
    {
        $this->logIn('Amira');

        foreach (['/dashboard', '/history', '/history?member=Amira'] as $path) {
            $this->client->request('GET', $path);
            self::assertResponseStatusCodeSame(403, $path);
        }
        self::assertSelectorNotExists('nav a[href="/dashboard"]');
    }
}
