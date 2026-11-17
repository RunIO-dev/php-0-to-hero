<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReportTest extends TestCase
{
    public function testTitles(): void
    {
        require_once subject(__DIR__, 'report.php');

        self::assertSame("== Titles ==\nKindred\nSula\n(2 rows)\n", (new TitlesReport(['Kindred', 'Sula']))->render());
        self::assertSame("== Titles ==\n(0 rows)\n", (new TitlesReport([]))->render());
    }

    public function testLoans(): void
    {
        require_once subject(__DIR__, 'report.php');

        self::assertSame("== Loans ==\nKindred: Amira\n(1 row)\n", (new LoansReport(['Kindred' => 'Amira']))->render());
    }

    public function testTheFrameIsFixed(): void
    {
        require_once subject(__DIR__, 'report.php');

        self::assertTrue((new ReflectionClass(Report::class))->isAbstract());
        self::assertTrue((new ReflectionMethod(Report::class, 'render'))->isFinal(), 'Make render() final.');
        self::assertTrue((new ReflectionMethod(Report::class, 'rows'))->isAbstract());
        self::assertTrue((new ReflectionMethod(Report::class, 'title'))->isAbstract());
    }
}
