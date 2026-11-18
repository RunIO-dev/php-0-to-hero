<?php

declare(strict_types=1);

namespace Shelf\MessageHandler;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Shelf\Library;
use Shelf\Message\SendReminders;
use Shelf\Web\ReminderMail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Sends the reminders that are due (chapter 85). Each is claimed first, then sent: if this runs twice,
 * because the worker retried it or two workers ran at once, each email still goes once. The email
 * itself goes into the queue, so a slow mail server holds up nothing here.
 */
#[AsMessageHandler]
final readonly class SendRemindersHandler
{
    public function __construct(
        private Library $library,
        private ReminderMail $mail,
        private ClockInterface $clock,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(SendReminders $message): void
    {
        $today = $this->clock->now();
        foreach ($this->library->remindersDue($today) as [$about, $kind]) {
            if (!$this->library->claimReminder($about, $today)) {
                continue; // sent already, a moment ago
            }
            $this->mail->send($about, $kind);
            $this->logger->info("Reminder {$kind->value}: \"{$about->book->title}\" to {$about->member->name}");
        }
    }
}
