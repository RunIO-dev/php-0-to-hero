<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** Checks your LendTest.php: it passes on lend.php and fails on each file in bugs/. */
final class BugsCaughtTest extends TestCase
{
    public function testYourTestsCatchEveryBug(): void
    {
        self::assertSame([], missed_bugs(__DIR__, 'LendTest.php', 'lend.php'));
    }
}
