<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Checks your ShelfCodeTest.php: one test with a data provider, which catches every bug. */
final class OneTestTest extends TestCase
{
    public function testOneTestWithADataProvider(): void
    {
        $file = subject(__DIR__, 'ShelfCodeTest.php');
        $code = (string) file_get_contents($file);
        preg_match_all('/function test\w*\(/', $code, $tests);
        self::assertCount(1, $tests[0], 'Keep one test method.');
        self::assertStringContainsString('#[DataProvider(', $code, 'Give it a data provider: #[DataProvider(\'books\')]');
        self::assertTrue(class_exists(DataProvider::class));
    }

    public function testYourTestsCatchEveryBug(): void
    {
        self::assertSame([], missed_bugs(__DIR__, 'ShelfCodeTest.php', 'shelf-code.php'));
    }
}
