<?php

declare(strict_types=1);

// Exercise 57, intermediate: the transaction pattern, written once.
//
// inTransaction($db, $work) starts a transaction, runs $work, commits, and returns what $work
// returned. If $work throws anything, it rolls back and throws the same exception on.

/**
 * @template T
 *
 * @param callable(): T $work
 *
 * @return T
 */
function inTransaction(PDO $db, callable $work): mixed
{
    return $work();
}
// Check your work: composer exercise 57 intermediate
