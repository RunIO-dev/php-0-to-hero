<?php

declare(strict_types=1);

// Exercise 32, advanced: a branch with its own books.
//
// This class has a bug: every branch shows the books of all branches, and
// the tests of one branch see the books of another. Find the cause and fix it,
// so that each branch has its own books. The methods stay as they are, and the
// class keeps no static properties.

final class Branch
{
    /** @var list<string> */
    private static array $titles = [];

    public function __construct(public readonly string $name) {}

    public function add(string $title): void
    {
        self::$titles[] = $title;
    }

    /** @return list<string> */
    public function titles(): array
    {
        return self::$titles;
    }
}

// Check your work: composer exercise 32 advanced
