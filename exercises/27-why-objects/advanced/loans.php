<?php

declare(strict_types=1);

// Exercise 27, advanced: the same object, or a copy.
//
// The class Book is given. Write two functions:
// - lendAll(array $books): void lends one copy of every book that still has
//   one: its copies go down by 1. It returns nothing: the caller's books change.
// - before(Book $book): Book returns a copy of the book that later changes to
//   the book don't touch, so a report can show the state before lending.

final class Book
{
    public string $title;
    public int $copies;
}

// Check your work: composer exercise 27 advanced
