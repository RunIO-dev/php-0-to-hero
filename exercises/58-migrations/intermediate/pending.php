<?php

declare(strict_types=1);

// Exercise 58, intermediate: which migrations still have to run?
//
// pending($files, $ran) gets the names of the files in migrations/ (in any order) and the names
// the database has run already, and returns the files that haven't run, in order of number.
// It throws a RuntimeException when:
//   - a file isn't named like 004-what-it-does.sql (three digits, a dash, lower-case words);
//   - two files have the same number (004-a.sql and 004-b.sql: which runs first?);
//   - a migration that ran has no file any more (deleted or renamed: the history is broken).

/**
 * @param list<string> $files
 * @param list<string> $ran
 *
 * @return list<string>
 */
function pending(array $files, array $ran): array
{
    return array_values(array_diff($files, $ran));
}
// Check your work: composer exercise 58 intermediate
