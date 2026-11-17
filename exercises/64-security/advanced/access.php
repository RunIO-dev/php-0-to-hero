<?php

declare(strict_types=1);

// Exercise 64, advanced: a check that fails closed.
//
// The tables: members (name TEXT PRIMARY KEY, role TEXT) and notes (id INTEGER PRIMARY KEY,
// member TEXT, text TEXT). mayOpen($db, $member, $noteId) says whether $member may open note
// $noteId: their own notes, and every note for staff.
//
// This version says yes when anything goes wrong. Make it fail closed: an error in the database must
// never become a yes, so let the exception go on to the caller. And a member who doesn't exist may
// open nothing.

function mayOpen(PDO $db, string $member, int $noteId): bool
{
    try {
        $find = $db->prepare('SELECT notes.member, members.role FROM notes, members WHERE notes.id = :id AND members.name = :member');
        $find->execute(['id' => $noteId, 'member' => $member]);
        $row = $find->fetch(PDO::FETCH_ASSOC);

        return $row === false || $row['member'] === $member || $row['role'] === 'staff';
    } catch (Throwable) {
        return true;
    }
}
// Check your work: composer exercise 64 advanced
