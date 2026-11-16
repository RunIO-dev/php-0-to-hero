<?php

declare(strict_types=1);

/**
 * @param list<array{title: string, author: string, year: int}> $books
 * @return list<array{title: string, author: string, year: int}>
 */
function byAuthorThenYear(array $books): array
{
    usort(
        $books,
        fn(array $a, array $b): int => strcasecmp($a['author'], $b['author']) ?: $a['year'] <=> $b['year'],
    );

    return $books;
}
