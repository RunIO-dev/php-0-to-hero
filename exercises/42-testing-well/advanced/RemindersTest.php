<?php

declare(strict_types=1);

// Exercise 42, advanced: fakes instead of the real world.
//
// Reminders needs a Clock and a Mailer. In a test, the real date changes every day
// and real mail must never be sent. Write two fakes in this file:
// - FixedClock: implements Clock, and always returns the date given to its constructor;
// - CollectingMailer: implements Mailer, and only collects what it was asked to send.
// Then test Reminders with them: who gets which text, how many were sent, and that
// loans due today, or the day after tomorrow, get no reminder. Use a fixed date that is
// not today. Each file in bugs/ is reminders.php with one mistake; catch them all.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/reminders.php';

final class RemindersTest extends TestCase
{
    public function testAReminderForALoanDueTomorrow(): void
    {
        self::markTestIncomplete('Write the fakes, then this test.');
    }

    // your other tests here
}

// Check your work: composer exercise 42 advanced
