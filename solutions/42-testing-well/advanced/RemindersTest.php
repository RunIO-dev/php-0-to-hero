<?php

declare(strict_types=1);

// Exercise 42, advanced: fakes instead of the real world. A solution.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/reminders.php';

final readonly class FixedClock implements Clock
{
    public function __construct(private DateTimeImmutable $today) {}

    public function today(): DateTimeImmutable
    {
        return $this->today;
    }
}

final class CollectingMailer implements Mailer
{
    /** @var list<array{string, string}> who got which text */
    public private(set) array $sent = [];

    public function send(string $to, string $text): void
    {
        $this->sent[] = [$to, $text];
    }
}

final class RemindersTest extends TestCase
{
    private CollectingMailer $mailer;
    private Reminders $reminders;

    protected function setUp(): void
    {
        $this->mailer = new CollectingMailer();
        $this->reminders = new Reminders(new FixedClock(new DateTimeImmutable('2020-05-04')), $this->mailer);
    }

    public function testAReminderForALoanDueTomorrow(): void
    {
        $count = $this->reminders->send([
            new Loan('Sula', 'Jonas', new DateTimeImmutable('2020-05-04')),
            new Loan('Kindred', 'Amira', new DateTimeImmutable('2020-05-05')),
        ]);

        self::assertSame(1, $count);
        self::assertSame([['Amira', 'Please bring back "Kindred" tomorrow.']], $this->mailer->sent);
    }

    public function testNoReminderForOtherDays(): void
    {
        $count = $this->reminders->send([
            new Loan('Sula', 'Jonas', new DateTimeImmutable('2020-05-04')),
            new Loan('Beloved', 'Ana', new DateTimeImmutable('2020-05-03')),
            new Loan('Kitchen', 'Kenji', new DateTimeImmutable('2020-05-06')),
        ]);

        self::assertSame(0, $count);
        self::assertSame([], $this->mailer->sent);
    }
}
