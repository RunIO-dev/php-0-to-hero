<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/late-fee.php';

final class ForgottenPrefixTest extends TestCase
{
    public function twentyCentsForEachDay(): void
    {
        self::assertSame(60, lateFee(3));
    }
}
