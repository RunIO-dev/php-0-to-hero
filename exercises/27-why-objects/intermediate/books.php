<?php

declare(strict_types=1);

// Exercise 27, intermediate: from arrays to objects.
//
// The class Book is given. Write two functions:
// - toBooks(array $rows): array turns a list of arrays, each with a title and
//   a year, into a list of Book objects, in the same order;
// - oldest(array $books): Book returns the book with the smallest year. If two
//   books have the same year, the first one wins.

final class Book
{
    public string $title;
    public int $year;
}

// Check your work: composer exercise 27 intermediate
