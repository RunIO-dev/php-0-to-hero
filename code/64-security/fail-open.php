<?php

declare(strict_types=1);

// ✗ When the check itself fails, it says yes: it fails open.

function mayRemoveBooks(PDO $db, string $member): bool
{
    try {
        $find = $db->prepare('SELECT role FROM members WHERE name = :name');
        $find->execute(['name' => $member]);

        return $find->fetchColumn() === 'staff';
    } catch (PDOException) {
        return true; // "don't block the librarians when the database has a problem"
    }
}

// The members table is missing: a typo, or a migration that didn't run.
$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo 'Amira may remove books: ', mayRemoveBooks($db, 'Amira') ? 'yes' : 'no', "\n";
