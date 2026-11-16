<?php

declare(strict_types=1);

$books = [
    ['title' => 'Things Fall Apart', 'copies' => 2],
    ['title' => 'Kindred', 'copies' => 0],
    ['title' => 'Kitchen', 'copies' => 1],
];

/**
 * @param list<array{title: string, copies: int}> $books
 * @return array{available: int, onLoan: int}
 */
function loanCounts(array $books): array
{
    $counts = ['available' => 0, 'onLoan' => 0];
    foreach ($books as $book) {
        if ($book['copies'] > 0) {
            $counts['available']++;
        } else {
            $counts['onLoan']++;
        }
    }

    return $counts;
}

$counts = loanCounts($books);
echo "{$counts['available']} available, {$counts['onLoan']} on loan\n";
