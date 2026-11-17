<?php

declare(strict_types=1);

// The two databases the exercise tests use: SQLite in memory, and MariaDB in Docker.
// Start MariaDB first: docker compose up -d --wait db

function sqlite(): PDO
{
    return new PDO('sqlite::memory:', options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
}

/** A connection to MariaDB as shelf_admin (may change tables) or shelf_app (rows only). */
function mariadb(string $user): PDO
{
    $password = (string) getenv($user === 'shelf_admin' ? 'SHELF_ADMIN_PASSWORD' : 'SHELF_APP_PASSWORD');

    return new PDO('mysql:host=db;dbname=shelf;charset=utf8mb4', $user, $password, [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

/** @return list<array<string, mixed>> */
function rowsOf(PDO $db, string $sql): array
{
    $result = $db->query($sql);

    return $result === false ? [] : array_values($result->fetchAll(PDO::FETCH_ASSOC));
}
