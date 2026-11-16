<?php

declare(strict_types=1);

/**
 * @param list<array{title: string, year: int}> $books
 * @return list<string>
 */
function titlesSince(array $books, int $year): array
{
    $newer = array_filter($books, fn(array $book): bool => $book['year'] >= $year);

    return array_values(array_map(fn(array $book): string => $book['title'], $newer));
}
