<?php

declare(strict_types=1);

// What an index changes (chapter 81). EXPLAIN QUERY PLAN tells how SQLite would answer a query,
// without answering it: SCAN reads every row, SEARCH and USING INDEX go straight to the right ones,
// and a TEMP B-TREE is a sort done for this query alone.

require_once __DIR__ . '/catalogue.php';

$db = catalogue();
$queries = [
    'by author' => 'SELECT title FROM books ORDER BY author, title',
    'newest' => 'SELECT title FROM books ORDER BY year DESC, title',
    'one author' => "SELECT title FROM books WHERE author = 'Toni Morrison'",
    'part of a name' => "SELECT title FROM books WHERE LOWER(author) LIKE '%morrison%'",
];
$plan = function (string $sql) use ($db): string {
    return implode('; ', array_column($db->query("EXPLAIN QUERY PLAN $sql")->fetchAll(), 'detail'));
};

foreach (['Without an index' => [], 'With indexes on author and year' => ['author', 'year']] as $heading => $columns) {
    foreach ($columns as $column) {
        $db->exec("CREATE INDEX books_$column ON books ($column)");
    }
    echo "$heading:\n";
    foreach ($queries as $name => $sql) {
        printf("  %-15s %s\n", $name, $plan($sql));
    }
}
