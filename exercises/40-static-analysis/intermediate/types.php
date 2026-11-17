<?php

declare(strict_types=1);

// Exercise 40, intermediate: arrays of what?
//
// PHPStan at level 6 asks what each array holds:
//   vendor/bin/phpstan analyse --level 6 exercises/40-static-analysis/intermediate/types.php
// Add PHPDoc types (@param and @return) to the three functions, so that it finds
// nothing. Each book is an array with a string "title" and an int "year".
// Don't change the code itself.

function titles(array $books): array
{
    return array_column($books, 'title');
}

function newest(array $books): array
{
    usort($books, fn(array $a, array $b): int => $b['year'] <=> $a['year']);

    return $books[0];
}

function countByDecade(array $books): array
{
    $counts = [];
    foreach ($books as $book) {
        $decade = intdiv($book['year'], 10) * 10;
        $counts[$decade] = ($counts[$decade] ?? 0) + 1;
    }

    return $counts;
}

// Check your work: composer exercise 40 intermediate
