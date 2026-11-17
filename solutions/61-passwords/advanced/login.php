<?php

declare(strict_types=1);

// Exercise 61, advanced: log in, and keep hashes up to date.

function logIn(PDO $db, string $name, string $password): bool
{
    $find = $db->prepare('SELECT password_hash FROM members WHERE name = :name');
    $find->execute(['name' => $name]);
    $hash = $find->fetchColumn();
    if (!is_string($hash) || !password_verify($password, $hash)) {
        return false;
    }
    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        $store = $db->prepare('UPDATE members SET password_hash = :hash WHERE name = :name');
        $store->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'name' => $name]);
    }

    return true;
}
