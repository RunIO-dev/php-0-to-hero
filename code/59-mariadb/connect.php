<?php

declare(strict_types=1);

// Connecting to MariaDB: only the DSN, the user and the password differ from SQLite.

require_once __DIR__ . '/db.php';

freshLibrary();
$db = connect('shelf_app');

$who = $db->query('SELECT CURRENT_USER() AS user, DATABASE() AS db, @@character_set_connection AS charset');
var_dump($who === false ? null : $who->fetch());

$books = $db->query('SELECT title, year FROM books ORDER BY year');
foreach ($books === false ? [] : $books as $row) {
    echo "{$row['title']} ({$row['year']})\n";
}
