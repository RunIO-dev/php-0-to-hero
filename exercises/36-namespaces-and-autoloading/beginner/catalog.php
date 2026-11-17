<?php

declare(strict_types=1);

// Exercise 36, beginner: a namespace.
//
// Put the class Book and the function dueDate below into the namespace
// Library\Catalog, so that their full names are Library\Catalog\Book and
// Library\Catalog\dueDate. Then make the file work again: inside a namespace,
// PHP's own DateTimeImmutable needs a use statement (or a leading backslash).

final readonly class Book
{
    public function __construct(public string $title) {}
}

function dueDate(DateTimeImmutable $lentOn): DateTimeImmutable
{
    return $lentOn->modify('+14 days');
}

// Check your work: composer exercise 36 beginner
