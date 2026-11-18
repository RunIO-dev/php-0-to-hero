<?php

declare(strict_types=1);

namespace Shelf\Tests\Messenger;

use DateTimeImmutable;
use Shelf\Library;
use Shelf\Member;
use Shelf\MemberRepository;
use Shelf\Message\ExpireHolds;
use Shelf\Message\SendReminders;
use Shelf\Schedule;
use Shelf\Tests\Web\ShelfWebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Scheduler\RecurringMessage;

/**
 * The reminders (chapter 85), as the worker handles them: the message goes to the bus, its handler
 * finds what is due and sends it. In the tests, the queue is handled at once (messenger.yaml), so
 * each email is there when dispatch() returns. Amira has a confirmed address; Jonas has none.
 */
final class RemindersTest extends ShelfWebTestCase
{
    use ClockSensitiveTrait;

    private Library $library;
    private MessageBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();
        $members = self::service(MemberRepository::class);
        $amira = $members->find('Amira') ?? self::fail('No Amira');
        $amira->confirmEmail('amira@example.org');
        $members->update($amira);
        $this->library = self::service(Library::class);
        $this->bus = self::service(MessageBusInterface::class);
    }

    private function amira(): Member
    {
        return self::service(MemberRepository::class)->find('Amira') ?? self::fail('No Amira');
    }

    public function testADueSoonReminderGoesOnce(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->lend('Kindred', 'Jonas', new DateTimeImmutable('2026-03-02'));
        self::mockTime('2026-03-14 08:00');

        $this->bus->dispatch(new SendReminders());
        $this->bus->dispatch(new SendReminders()); // the next hour, or a second worker

        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', 'amira@example.org');
        self::assertEmailHeaderSame($email, 'Subject', '"Kindred" is due back on Monday, 16 March');
        self::assertEmailTextBodyContains($email, 'http://localhost:8000/loans');
    }

    public function testAnOverdueBookIsRemindedOfWeekly(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        foreach (['2026-03-17', '2026-03-18', '2026-03-24'] as $day) {
            self::mockTime("$day 08:00");
            $this->bus->dispatch(new SendReminders());
        }

        self::assertEmailCount(2);
        self::assertEmailHeaderSame(self::getMailerMessage(1) ?? self::fail('No second email'), 'Subject', '"Kindred" was due back on Monday, 16 March');
    }

    public function testAnExpiredCopyGoesOnAndItsNextMemberHearsOfIt(): void
    {
        $this->library->lend('Kindred', 'Jonas', new DateTimeImmutable('2026-03-02'));
        $jonass = $this->library->lend('Kindred', 'Jonas', new DateTimeImmutable('2026-03-02'));
        $this->library->reserve('Kindred', $this->amira(), new DateTimeImmutable('2026-03-03'));
        $this->library->giveBack($jonass->id(), new DateTimeImmutable('2026-03-04'));

        self::mockTime('2026-03-04 09:00');
        $this->bus->dispatch(new SendReminders());

        self::assertEmailCount(1);
        self::assertEmailHeaderSame(self::getMailerMessage() ?? self::fail('No email'), 'Subject', '"Kindred" is waiting for you at the desk, until Wednesday, 11 March');

        self::mockTime('2026-03-12 03:00');
        $this->bus->dispatch(new ExpireHolds());
        self::assertSame([], $this->library->holdsOf($this->amira()), 'Expired');
        self::assertSame(1, $this->library->find('Kindred')->copies, 'Nobody else waits: on the shelf');
    }

    public function testTheScheduleSendsRemindersHourlyAndExpiresDaily(): void
    {
        $triggers = array_map(
            fn(RecurringMessage $message): string => (string) $message->getTrigger(),
            self::service(Schedule::class)->getSchedule()->getRecurringMessages(),
        );

        self::assertSame(['every 1 hour', 'every 1 day'], array_values($triggers));
    }
}
