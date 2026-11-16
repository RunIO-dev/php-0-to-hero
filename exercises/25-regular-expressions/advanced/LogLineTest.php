<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LogLineTest extends TestCase
{
    public function testReadsALendLine(): void
    {
        require_once subject(__DIR__, 'log-line.php');

        self::assertSame(
            ['date' => '2026-11-17', 'action' => 'LEND', 'title' => 'Kindred', 'email' => 'amira@example.org'],
            parseLogLine('2026-11-17 LEND "Kindred" amira@example.org'),
        );
    }

    public function testReadsAReturnWithSpacesInTheTitle(): void
    {
        require_once subject(__DIR__, 'log-line.php');

        self::assertSame(
            ['date' => '2026-11-20', 'action' => 'RETURN', 'title' => 'Things Fall Apart', 'email' => 'jonas@example.org'],
            parseLogLine('2026-11-20 RETURN "Things Fall Apart" jonas@example.org'),
        );
    }

    /** @return array<string, array{string}> */
    public static function broken(): array
    {
        return [
            'another action' => ['2026-11-17 BURN "Kindred" amira@example.org'],
            'no quotes' => ['2026-11-17 LEND Kindred amira@example.org'],
            'no email' => ['2026-11-17 LEND "Kindred"'],
            'not an email' => ['2026-11-17 LEND "Kindred" amira'],
            'a short date' => ['26-11-17 LEND "Kindred" amira@example.org'],
            'more after it' => ['2026-11-17 LEND "Kindred" amira@example.org extra'],
            'a line break' => ["2026-11-17 LEND \"Kindred\" amira@example.org\n"],
            'empty' => [''],
        ];
    }

    #[DataProvider('broken')]
    public function testOtherShapesGiveNull(string $line): void
    {
        require_once subject(__DIR__, 'log-line.php');

        self::assertNull(parseLogLine($line));
    }
}
