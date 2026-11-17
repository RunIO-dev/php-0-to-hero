<?php

declare(strict_types=1);

// Exercise 57, intermediate: the transaction pattern, written once.

/**
 * @template T
 *
 * @param callable(): T $work
 *
 * @return T
 */
function inTransaction(PDO $db, callable $work): mixed
{
    $db->beginTransaction();
    try {
        $result = $work();
        $db->commit();

        return $result;
    } catch (Throwable $e) {
        $db->rollBack();

        throw $e;
    }
}
