<?php

declare(strict_types=1);

// The application's user can read and write rows, and nothing more.

require_once __DIR__ . '/db.php';

freshLibrary();
$db = connect('shelf_app');

$grants = $db->query('SHOW GRANTS');
foreach ($grants === false ? [] : $grants->fetchAll(PDO::FETCH_COLUMN) as $grant) {
    if (is_string($grant) && !str_starts_with($grant, 'GRANT USAGE')) { // USAGE: may connect, and holds the password's hash
        echo $grant, "\n";
    }
}

foreach (['DELETE FROM books WHERE copies = 0', 'DROP TABLE books', 'CREATE TABLE notes (id INT)'] as $sql) {
    try {
        $rows = $db->exec($sql);
        echo "$sql: $rows row(s)\n";
    } catch (PDOException $e) {
        // The message names the computer the request came from; it differs on every network.
        echo "$sql: ", preg_replace("/'shelf_app'@'[^']*'/", "'shelf_app'@'…'", $e->errorInfo[2] ?? ''), "\n";
    }
}
