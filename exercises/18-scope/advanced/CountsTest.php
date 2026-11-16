<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CountsTest extends TestCase
{
    public function testPrintsBothCounts(): void
    {
        self::assertSame("2 available, 1 on loan\n", output_of(subject(__DIR__, 'counts.php')));
    }

    public function testOtherBooks(): void
    {
        $books = "[['title' => 'Sula', 'copies' => 0], ['title' => 'Beloved', 'copies' => 0]]";

        self::assertSame("0 available, 2 on loan\n", output_with(subject(__DIR__, 'counts.php'), ['books' => $books]));
    }

    public function testReturnsAMap(): void
    {
        ob_start();
        try {
            require_once subject(__DIR__, 'counts.php');
        } finally {
            ob_end_clean();
        }

        self::assertTrue(function_exists('loanCounts'), 'Write a function named loanCounts.');
        self::assertSame(['available' => 1, 'onLoan' => 0], loanCounts([['title' => 'Kitchen', 'copies' => 3]]));
    }
}
