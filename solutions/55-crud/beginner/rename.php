<?php

declare(strict_types=1);

// Exercise 55, beginner: correct a book's title.

function renameBook(PDO $db, string $from, string $to): bool
{
    $rename = $db->prepare('UPDATE books SET title = :to WHERE title = :from');
    $rename->execute(['from' => $from, 'to' => $to]);

    return $rename->rowCount() === 1;
}
