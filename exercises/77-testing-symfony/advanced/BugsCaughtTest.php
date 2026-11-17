<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** Checks your SuggestionsTest.php: it passes on suggestions.php and fails on each file in bugs/. */
final class BugsCaughtTest extends TestCase
{
    public function testYourTestsCatchEveryBug(): void
    {
        self::assertSame([], missed_bugs(__DIR__, 'SuggestionsTest.php', 'suggestions.php'));
    }
}
