<?php

declare(strict_types=1);

// Exercise 40, intermediate: arrays of what? A solution.

/**
 * @param list<array{title: string, year: int}> $books
 * @return list<string>
 */
function titles(array $books): array
{
    return array_column($books, 'title');
}

/**
 * @param non-empty-list<array{title: string, year: int}> $books
 * @return array{title: string, year: int}
 */
function newest(array $books): array
{
    usort($books, fn(array $a, array $b): int => $b['year'] <=> $a['year']);

    return $books[0];
}

/**
 * @param list<array{title: string, year: int}> $books
 * @return array<int, int> decade => number of books
 */
function countByDecade(array $books): array
{
    $counts = [];
    foreach ($books as $book) {
        $decade = intdiv($book['year'], 10) * 10;
        $counts[$decade] = ($counts[$decade] ?? 0) + 1;
    }

    return $counts;
}
