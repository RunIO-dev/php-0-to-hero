<?php

declare(strict_types=1);

// Storing and finding objects: Doctrine writes the SQL.

use Library\Book;

require_once __DIR__ . '/entities.php';

$entities = entities(showSql: true);

echo "persist, then flush:\n";
$kindred = new Book('Kindred', 'Octavia E. Butler', 2);
$entities->persist($kindred);                    // from now on, Doctrine looks after it
$entities->persist(new Book('Sula', 'Toni Morrison'));
$entities->flush();                              // and here, it writes: both INSERTs, in one transaction
echo "Kindred got the ID $kindred->id\n";

echo "find by a column:\n";
$entities->clear();                              // forget every object, so the next find reads the table
$found = $entities->getRepository(Book::class)->findOneBy(['title' => 'Sula']);
echo "Found: $found?->title by $found?->author\n";
