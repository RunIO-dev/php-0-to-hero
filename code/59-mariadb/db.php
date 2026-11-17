<?php

declare(strict_types=1);

// How this chapter's examples reach MariaDB: the container named db, from compose.yaml.
// Start it first: docker compose up -d --wait db

/** A connection as one of the database's two users: shelf_app or shelf_admin. */
function connect(string $user): PDO
{
    $password = getenv($user === 'shelf_admin' ? 'SHELF_ADMIN_PASSWORD' : 'SHELF_APP_PASSWORD');
    if ($password === false) {
        throw new RuntimeException('No password in the environment: run this in Docker, as: docker compose run --rm php php ...');
    }

    return new PDO('mysql:host=db;dbname=shelf;charset=utf8mb4', $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

/** The books table as library.sql makes it, with its four books. Only shelf_admin may do this. */
function freshLibrary(): void
{
    connect('shelf_admin')->exec((string) file_get_contents(__DIR__ . '/library.sql'));
}
