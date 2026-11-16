<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SortTest extends TestCase
{
    public function testSortsByAuthorThenYear(): void
    {
        require_once subject(__DIR__, 'sort.php');
        $books = [
            ['title' => 'Parable of the Sower', 'author' => 'Octavia E. Butler', 'year' => 1993],
            ['title' => 'Kitchen', 'author' => 'banana Yoshimoto', 'year' => 1988],
            ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979],
            ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958],
        ];

        self::assertSame(
            ['Kitchen', 'Things Fall Apart', 'Kindred', 'Parable of the Sower'],
            array_column(byAuthorThenYear($books), 'title'),
        );
    }

    public function testUsesUsort(): void
    {
        $code = (string) file_get_contents(subject(__DIR__, 'sort.php'));
        $code = (string) preg_replace('~^//.*$~m', '', $code);

        self::assertStringContainsString('usort(', $code, 'Sort with usort.');
        self::assertStringContainsString('fn(', $code, 'Compare with an arrow function.');
    }
}
