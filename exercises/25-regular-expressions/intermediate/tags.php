<?php

declare(strict_types=1);

// Exercise 25, intermediate: tags as typed.
//
// Librarians type a book's tags in one line, separated by commas or
// semicolons, with any spaces: " History ;africa,,  Novel "
// Write a function tags(string $typed): array that returns the tags in small
// letters, without empty ones, in the order typed, each only once:
//   ['history', 'africa', 'novel']
//
// Check your work: composer exercise 25 intermediate
