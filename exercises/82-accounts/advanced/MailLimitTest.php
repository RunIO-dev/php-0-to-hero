<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'MailLimit.php');

final class MailLimitTest extends TestCase
{
    public function testAKeyMaySendUpToTheLimit(): void
    {
        $limit = new MailLimit(3, 3600);

        self::assertSame([true, true, true, false, false], array_map(fn(int $t): bool => $limit->allow('10.0.0.1', $t), [0, 10, 20, 30, 40]));
        self::assertSame(3, $limit->kept('10.0.0.1'), 'Refused messages are not counted');
    }

    public function testTheWindowSlides(): void
    {
        $limit = new MailLimit(3, 3600);
        foreach ([0, 600, 1200] as $t) {
            $limit->allow('ip', $t);
        }

        self::assertSame(1800, $limit->retryAfter('ip', 1800));
        self::assertFalse($limit->allow('ip', 3599));
        self::assertSame(0, $limit->retryAfter('ip', 3600));
        self::assertTrue($limit->allow('ip', 3600), 'The first one left the window');
        self::assertFalse($limit->allow('ip', 4100));
        self::assertTrue($limit->allow('ip', 4200));
    }

    public function testKeysAreCountedApart(): void
    {
        $limit = new MailLimit(1, 60);

        self::assertTrue($limit->allow('a', 0));
        self::assertTrue($limit->allow('b', 0));
        self::assertFalse($limit->allow('a', 30));
        self::assertSame(30, $limit->retryAfter('a', 30));
        self::assertSame(0, $limit->retryAfter('c', 30));
    }

    public function testOldTimesAreForgotten(): void
    {
        $limit = new MailLimit(5, 60);
        foreach (range(0, 1000, 30) as $t) {
            $limit->allow('ip', $t);
        }

        self::assertLessThanOrEqual(5, $limit->kept('ip'));
        self::assertSame(2, $limit->kept('ip'), 'Only 960 and 990 are inside the minute before 1000');
    }
}
