<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** Checks your RemindersTest.php: it passes on reminders.php and fails on each file in bugs/. */
final class BugsCaughtTest extends TestCase
{
    public function testYourTestsCatchEveryBug(): void
    {
        self::assertSame([], missed_bugs(__DIR__, 'RemindersTest.php', 'reminders.php'));
    }
}
