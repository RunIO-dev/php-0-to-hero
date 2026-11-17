<?php

declare(strict_types=1);

// Exercise 54, intermediate: add a book, and return the id the database gave it.

function addBook(PDO $db, string $title, string $author, int $year, int $copies = 1): int
{
    $add = $db->prepare('INSERT INTO books (title, author, year, copies) VALUES (:title, :author, :year, :copies)');
    $add->execute(['title' => $title, 'author' => $author, 'year' => $year, 'copies' => $copies]);

    return (int) $db->lastInsertId();
}
