<?php

declare(strict_types=1);

// Performance (chapter 89): measure before you change anything. Here the measure is the number of
// queries: one list of members with their loans, read in two ways.

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('CREATE TABLE members (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
$db->exec('CREATE TABLE loans (member_id INTEGER NOT NULL REFERENCES members (id), title TEXT NOT NULL)');
$db->exec("INSERT INTO members (id, name) VALUES (1, 'Amira'), (2, 'Jonas'), (3, 'Sula')");
$db->exec("INSERT INTO loans VALUES (1, 'Kindred'), (1, 'Beloved'), (2, 'Things Fall Apart'), (3, 'Sula')");

$queries = 0;

/**
 * @param list<scalar> $values
 * @return list<array<string, scalar>>
 */
function rows(PDO $db, string $sql, array $values, int &$queries): array
{
    $queries++;
    $statement = $db->prepare($sql);
    $statement->execute($values);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

// One query for the members, then one more for each member's loans: "N + 1" queries.
foreach (rows($db, 'SELECT id, name FROM members ORDER BY id', [], $queries) as $member) {
    $titles = array_column(rows($db, 'SELECT title FROM loans WHERE member_id = ? ORDER BY title', [$member['id']], $queries), 'title');
    echo $member['name'], ': ', implode(', ', $titles), "\n";
}
echo "N + 1: $queries queries for 3 members\n";

// One query with a JOIN: the same list.
$queries = 0;
$loans = [];
foreach (rows($db, 'SELECT m.name, l.title FROM members m JOIN loans l ON l.member_id = m.id ORDER BY m.id, l.title', [], $queries) as $row) {
    $loans[$row['name']][] = $row['title'];
}
foreach ($loans as $name => $titles) {
    echo $name, ': ', implode(', ', $titles), "\n";
}
echo "JOIN: $queries query for 3 members\n";
