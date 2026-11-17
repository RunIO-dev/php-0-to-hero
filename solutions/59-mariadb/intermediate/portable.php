<?php

declare(strict_types=1);

// Exercise 59, intermediate: one function, two databases.

function saveCopies(PDO $db, string $title, int $copies): void
{
    $sql = match ($db->getAttribute(PDO::ATTR_DRIVER_NAME)) {
        'sqlite' => 'INSERT INTO exercise_copies (title, copies) VALUES (:title, :copies)
                     ON CONFLICT (title) DO UPDATE SET copies = excluded.copies',
        'mysql' => 'INSERT INTO exercise_copies (title, copies) VALUES (:title, :copies)
                    ON DUPLICATE KEY UPDATE copies = VALUE(copies)',
        default => throw new LogicException('saveCopies() knows SQLite and MariaDB only'),
    };
    $save = $db->prepare($sql);
    $save->execute(['title' => $title, 'copies' => $copies]);
}
