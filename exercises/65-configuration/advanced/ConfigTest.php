<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'config.php');

final class ConfigTest extends TestCase
{
    public function testGoodSettings(): void
    {
        self::assertEquals(new LibraryConfig('North Library', 14, false), LibraryConfig::fromSettings(['LIBRARY_NAME' => 'North Library', 'LOAN_DAYS' => '14']));
        self::assertTrue(LibraryConfig::fromSettings(['LIBRARY_NAME' => 'North', 'LOAN_DAYS' => '90', 'LIBRARY_DEBUG' => 'yes'])->debug);
    }

    public function testEveryProblemIsNamedAtOnce(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("LIBRARY_NAME is missing\nLOAN_DAYS must be a whole number from 1 to 90\nLIBRARY_DEBUG must be yes or no");

        LibraryConfig::fromSettings(['LIBRARY_NAME' => '  ', 'LOAN_DAYS' => 'fourteen', 'LIBRARY_DEBUG' => 'true']);
    }

    public function testDaysOutOfRange(): void
    {
        foreach (['0', '91', '1.5', ''] as $days) {
            try {
                LibraryConfig::fromSettings(['LIBRARY_NAME' => 'North', 'LOAN_DAYS' => $days]);
                self::fail("LOAN_DAYS=$days must be refused");
            } catch (UnexpectedValueException $e) {
                self::assertSame('LOAN_DAYS must be a whole number from 1 to 90', $e->getMessage());
            }
        }
    }
}
