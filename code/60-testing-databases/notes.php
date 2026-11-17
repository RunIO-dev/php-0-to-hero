<?php

declare(strict_types=1);

// A small class that keeps a member's notes in a database: what the tests in this folder test.

final readonly class Notes
{
    public const string TABLE = 'CREATE TABLE IF NOT EXISTS notes (
        id INTEGER PRIMARY KEY,
        member TEXT NOT NULL,
        text TEXT NOT NULL CHECK (text <> \'\')
    )';

    public function __construct(private PDO $db) {}

    public function add(string $member, string $text): int
    {
        $add = $this->db->prepare('INSERT INTO notes (member, text) VALUES (:member, :text)');
        $add->execute(['member' => $member, 'text' => trim($text)]);

        return (int) $this->db->lastInsertId();
    }

    /** @return list<string> the member's notes, the oldest first */
    public function of(string $member): array
    {
        $find = $this->db->prepare('SELECT text FROM notes WHERE member = :member ORDER BY id');
        $find->execute(['member' => $member]);

        return array_values(array_map(strval(...), $find->fetchAll(PDO::FETCH_COLUMN)));
    }
}
