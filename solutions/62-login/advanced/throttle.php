<?php

declare(strict_types=1);

// Exercise 62, advanced: slow down password guessing.

const MAX_FAILURES = 5;
const WINDOW_MINUTES = 15;

function recordFailure(PDO $db, string $name, DateTimeImmutable $now): void
{
    $record = $db->prepare('INSERT INTO login_failures (name, at) VALUES (:name, :at)');
    $record->execute(['name' => mb_strtolower($name), 'at' => $now->format('Y-m-d H:i:s')]);
}

function isBlocked(PDO $db, string $name, DateTimeImmutable $now): bool
{
    $count = $db->prepare('SELECT COUNT(*) FROM login_failures WHERE name = :name AND at > :since');
    $count->execute(['name' => mb_strtolower($name), 'since' => $now->modify('-' . WINDOW_MINUTES . ' minutes')->format('Y-m-d H:i:s')]);

    return $count->fetchColumn() >= MAX_FAILURES;
}

function forgetFailures(PDO $db, string $name): void
{
    $db->prepare('DELETE FROM login_failures WHERE name = :name')->execute(['name' => mb_strtolower($name)]);
}
