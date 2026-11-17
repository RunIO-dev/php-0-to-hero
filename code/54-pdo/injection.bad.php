<?php

declare(strict_types=1);

// ✗ The visitor's text is pasted into the SQL. Never do this.

$db = new PDO('sqlite:' . __DIR__ . '/library.sqlite');
$author = $_GET['author'] ?? '';

$result = $db->query("SELECT title FROM books WHERE author = '$author'");
foreach ($result ?: [] as $row) {
    echo htmlspecialchars($row['title']), "\n";
}
