<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FixScopeTest extends TestCase
{
    public function testPrintsTheFee(): void
    {
        self::assertSame("Fee: 150 cents\n", output_of(subject(__DIR__, 'fix-scope.php')));
    }

    public function testUsesTheValueItIsGiven(): void
    {
        self::assertSame("Fee: 60 cents\n", output_with(subject(__DIR__, 'fix-scope.php'), ['perDay' => '20']));
    }
}
