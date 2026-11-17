<?php

declare(strict_types=1);

// Exercise 54, beginner: the titles by one author, in alphabetical order.
//
// The author's name comes from a visitor, so it may contain a quote (Flann O'Brien) or an
// attack (' OR '1'='1). Use a prepared statement with a placeholder.

/** @return list<string> */
function titlesBy(PDO $db, string $author): array
{
    return [];
}
// Check your work: composer exercise 54 beginner
