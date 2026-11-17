<?php

declare(strict_types=1);

// Exercise 63, advanced: notes that belong to someone.
//
// The table: notes (id INTEGER PRIMARY KEY, member TEXT NOT NULL, text TEXT NOT NULL).
// $member is the name of the member who is logged in, and $role their role, 'reader' or 'staff',
// both from the session. Use prepared statements.
//
// - notesOf($db, $member): the texts of the member's own notes, oldest first.
// - deleteNote($db, $id, $member, $role): deletes note $id if it is the member's own, or if the
//   member is staff. Returns whether a note was deleted. Check and delete in one statement.

/** @return list<string> */
function notesOf(PDO $db, string $member): array
{
    $find = $db->query('SELECT text FROM notes ORDER BY id');

    return $find === false ? [] : array_values(array_map(strval(...), $find->fetchAll(PDO::FETCH_COLUMN)));
}

function deleteNote(PDO $db, int $id, string $member, string $role): bool
{
    $delete = $db->prepare('DELETE FROM notes WHERE id = :id');
    $delete->execute(['id' => $id]);

    return $delete->rowCount() === 1;
}
// Check your work: composer exercise 63 advanced
