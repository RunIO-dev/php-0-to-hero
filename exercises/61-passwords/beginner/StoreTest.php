<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'store.php');

final class StoreTest extends TestCase
{
    private const string PASSWORD = 'tea kettle under the lemon tree';

    public function testTheStoredValueIsAHashNotThePassword(): void
    {
        $stored = storable(self::PASSWORD);

        self::assertStringNotContainsString('lemon', $stored);
        self::assertSame(password_get_info(password_hash('x', PASSWORD_DEFAULT))['algoName'], password_get_info($stored)['algoName'], 'Use password_hash with PASSWORD_DEFAULT');
    }

    public function testTheSamePasswordGetsANewSaltEachTime(): void
    {
        self::assertNotSame(storable(self::PASSWORD), storable(self::PASSWORD));
    }

    public function testOnlyTheRightPasswordMatches(): void
    {
        $stored = storable(self::PASSWORD);

        self::assertTrue(matches(self::PASSWORD, $stored));
        self::assertFalse(matches('Tea kettle under the lemon tree', $stored));
        self::assertFalse(matches($stored, $stored), 'Typing the stored hash must not log anyone in');
    }
}
