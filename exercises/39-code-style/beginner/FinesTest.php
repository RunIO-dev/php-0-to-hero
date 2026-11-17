<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Tools\Run;

final class FinesTest extends TestCase
{
    public function testTheFixerFindsNothing(): void
    {
        $root = dirname(__DIR__, 3);
        $run = Run::capture([PHP_BINARY, "$root/vendor/bin/php-cs-fixer", 'check', '--diff', '--no-ansi',
            '--using-cache=no', '--path-mode=override', subject(__DIR__, 'fines.php')], $root);

        self::assertSame(0, $run['code'], "The fixer would still change this:\n" . $run['out']);
    }

    public function testItPrintsTheSame(): void
    {
        self::assertSame("60\n500\n0\n", output_of(subject(__DIR__, 'fines.php')));
    }
}
