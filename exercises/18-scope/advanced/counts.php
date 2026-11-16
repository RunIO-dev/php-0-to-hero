<?php

declare(strict_types=1);

// Exercise 18, advanced: two answers from one function.
//
// countBooks() tries to change $available and $onLoan outside the function.
// That can't work: inside, they are new, undefined variables.
// Rewrite it as a function loanCounts($books) that returns both numbers in
// one map, ['available' => 2, 'onLoan' => 1], and print with it:
//   2 available, 1 on loan
// A book counts as available when it has at least one copy.
//
// Check your work: composer exercise 18 advanced

$books = [
    ['title' => 'Things Fall Apart', 'copies' => 2],
    ['title' => 'Kindred', 'copies' => 0],
    ['title' => 'Kitchen', 'copies' => 1],
];

$available = 0;
$onLoan = 0;

/** @param list<array{title: string, copies: int}> $books */
function countBooks(array $books): void
{
    foreach ($books as $book) {
        if ($book['copies'] > 0) {
            $available++;
        } else {
            $onLoan++;
        }
    }
}

countBooks($books);
echo "$available available, $onLoan on loan\n";
