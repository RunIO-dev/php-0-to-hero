<?php

declare(strict_types=1);

// Exercise 64, advanced: a check that fails closed.

function mayOpen(PDO $db, string $member, int $noteId): bool
{
    $find = $db->prepare('SELECT notes.member, members.role FROM notes, members WHERE notes.id = :id AND members.name = :member');
    $find->execute(['id' => $noteId, 'member' => $member]);
    $row = $find->fetch(PDO::FETCH_ASSOC);

    return is_array($row) && ($row['member'] === $member || $row['role'] === 'staff');
}
