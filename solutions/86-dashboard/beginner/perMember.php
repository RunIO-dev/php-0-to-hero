<?php

declare(strict_types=1);

// Loans per member (chapter 86), from the table of the test: loans (id, title, member, lent_on).
// One query with GROUP BY: [member => number of loans], the most first; equal counts A to Z.

/** @return array<string, int> */
function loansPerMember(PDO $db): array
{
    $query = $db->prepare('SELECT member, COUNT(*) FROM loans GROUP BY member ORDER BY COUNT(*) DESC, member');
    $query->execute();

    return array_map(intval(...), $query->fetchAll(PDO::FETCH_KEY_PAIR));
}
