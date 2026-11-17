<?php

declare(strict_types=1);

// The same check without the catch: when it can't decide, the error stops the request.

function mayRemoveBooks(PDO $db, string $member): bool
{
    $find = $db->prepare('SELECT role FROM members WHERE name = :name');
    $find->execute(['name' => $member]);

    return $find->fetchColumn() === 'staff';
}

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try {
    $mayRemove = mayRemoveBooks($db, 'Amira');
    echo 'Amira may remove books: ', $mayRemove ? 'yes' : 'no', "\n";
} catch (PDOException $e) {
    echo 'Refused, and noted for the log: ', $e->getMessage(), "\n";
}
