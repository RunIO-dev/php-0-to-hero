<?php

declare(strict_types=1);

// The unit of work: Doctrine remembers each object it loaded, and at flush() writes what changed.

use Library\Book;

require_once __DIR__ . '/entities.php';

$entities = entities();
$entities->persist(new Book('Kindred', 'Octavia E. Butler', 2));
$entities->flush();
$entities->clear();

$books = $entities->getRepository(Book::class);
$first = $books->findOneBy(['title' => 'Kindred']);
$again = $books->find($first?->id);
var_dump($first === $again);                     // one row, one object: the identity map

$first?->lend();                                 // changes the object only
echo 'In the table: ', $entities->getConnection()->fetchOne('SELECT copies FROM books'), "\n";
$entities->flush();                              // UPDATE books SET copies = 1 ...
echo 'After flush: ', $entities->getConnection()->fetchOne('SELECT copies FROM books'), "\n";
