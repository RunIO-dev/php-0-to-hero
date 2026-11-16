<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testBothScriptsPrintTheSame(): void
    {
        self::assertSame("Late fees today: 3.50 EUR\n", output_of(subject(__DIR__, 'fees.php')));
        self::assertSame("Paid: 12.50 EUR\n", output_of(subject(__DIR__, 'receipt.php')));
    }

    public function testMoneyIsDefinedOnceInMoneyPhp(): void
    {
        $dir = dirname(subject(__DIR__, 'fees.php'));
        self::assertFileExists("$dir/money.php", 'Create money.php in this folder.');
        self::assertStringContainsString('function money(', (string) file_get_contents("$dir/money.php"));

        foreach (['fees.php', 'receipt.php'] as $script) {
            $code = (string) file_get_contents("$dir/$script");
            self::assertStringNotContainsString('function money(', $code, "$script: remove money() here");
            self::assertStringContainsString("require_once __DIR__ . '/money.php';", $code, "$script: load money.php");
        }
    }
}
