<?php

declare(strict_types=1);

// Exercise 59, beginner: the application's connection to MariaDB.

function appConnection(): PDO
{
    $password = getenv('SHELF_APP_PASSWORD');
    if ($password === false) {
        throw new RuntimeException('Set the environment variable SHELF_APP_PASSWORD');
    }

    return new PDO('mysql:host=db;dbname=shelf;charset=utf8mb4', 'shelf_app', $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
