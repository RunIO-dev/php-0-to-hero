<?php

declare(strict_types=1);

// ✗ A queue's handler that isn't safe to run twice. A queue delivers a message at least once: if the
// worker stops after the email but before it marks the message as done, the message comes again, and
// the member gets the same reminder twice. The fix: remember what was done, and check it first.

final readonly class SendReminderHandler
{
    public function __construct(private Mailer $mailer) {}

    public function __invoke(SendReminder $reminder): void
    {
        $this->mailer->send($reminder->member, "Please return {$reminder->title}.");
    }
}
