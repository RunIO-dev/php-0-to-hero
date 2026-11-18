<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'settings.php');

final class SettingsTest extends TestCase
{
    private const string GOOD = <<<'INI'
        ; Shelf in production
        display_errors = Off
        log_errors = On
        expose_php = Off
        [opcache]
        opcache.validate_timestamps = 0
        INI;

    public function testAGoodFileHasNoProblems(): void
    {
        self::assertSame([], problems(self::GOOD));
    }

    public function testEveryProblemInOrder(): void
    {
        $ini = "display_errors = On\nlog_errors = Off\nexpose_php = On\nopcache.validate_timestamps = 1\n";

        self::assertSame([
            'display_errors must be Off',
            'log_errors must be On',
            'expose_php must be Off',
            'opcache.validate_timestamps must be 0',
        ], problems($ini));
    }

    public function testMissingSettingsAreProblemsToo(): void
    {
        self::assertSame(
            ['display_errors must be Off', 'opcache.validate_timestamps must be 0'],
            problems("log_errors = On\nexpose_php = Off\n"),
        );
    }

    public function testEveryWayOfWritingOnAndOff(): void
    {
        self::assertSame([], problems("display_errors = no\nlog_errors = yes\nexpose_php = false\nopcache.validate_timestamps = 0\n"));
        self::assertSame([], problems("display_errors = 0\nlog_errors = 1\nexpose_php = \"\"\nopcache.validate_timestamps = Off\n"));
        self::assertSame(['display_errors must be Off'], problems("display_errors = stderr\nlog_errors = true\nexpose_php = Off\nopcache.validate_timestamps = 0\n"));
    }
}
