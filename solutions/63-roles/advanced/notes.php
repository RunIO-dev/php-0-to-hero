<?php

declare(strict_types=1);

// Exercise 63, advanced: notes that belong to someone.

/** @return list<string> */
function notesOf(PDO $db, string $member): array
{
    $find = $db->prepare('SELECT text FROM notes WHERE member = :member ORDER BY id');
    $find->execute(['member' => $member]);

    return array_values(array_map(strval(...), $find->fetchAll(PDO::FETCH_COLUMN)));
}

function deleteNote(PDO $db, int $id, string $member, string $role): bool
{
    $delete = $db->prepare('DELETE FROM notes WHERE id = :id AND (member = :member OR :role = \'staff\')');
    $delete->execute(['id' => $id, 'member' => $member, 'role' => $role]);

    return $delete->rowCount() === 1;
}
