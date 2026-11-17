<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'notes.php');

final class NotesTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec("CREATE TABLE notes (id INTEGER PRIMARY KEY, member TEXT NOT NULL, text TEXT NOT NULL);
            INSERT INTO notes (member, text) VALUES ('Amira', 'Wants the next Butler'), ('Jonas', 'Owes a fine'), ('Amira', 'Back on Monday')");
    }

    public function testAMemberSeesOnlyTheirNotes(): void
    {
        self::assertSame(['Wants the next Butler', 'Back on Monday'], notesOf($this->db, 'Amira'));
        self::assertSame([], notesOf($this->db, "Amira' OR '1'='1"));
    }

    public function testAMemberDeletesOnlyTheirOwnNote(): void
    {
        self::assertFalse(deleteNote($this->db, 2, 'Amira', 'reader'), "Note 2 is Jonas's");
        self::assertTrue(deleteNote($this->db, 1, 'Amira', 'reader'));
        self::assertSame(['Back on Monday'], notesOf($this->db, 'Amira'));
        self::assertSame(['Owes a fine'], notesOf($this->db, 'Jonas'));
    }

    public function testStaffMayDeleteAnyNote(): void
    {
        self::assertTrue(deleteNote($this->db, 1, 'Jonas', 'staff'));
        self::assertFalse(deleteNote($this->db, 99, 'Jonas', 'staff'));
        self::assertSame(['Back on Monday'], notesOf($this->db, 'Amira'));
    }
}
