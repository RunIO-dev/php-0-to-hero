<?php

declare(strict_types=1);

// A placeholder between quotes is text, not a placeholder.

$db = new PDO('sqlite::memory:');
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

$find = $db->prepare("SELECT title FROM books WHERE author = ':author'");
$find->execute(['author' => 'Toni Morrison']);
