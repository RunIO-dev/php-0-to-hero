<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'access.php');

final class AccessTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec("CREATE TABLE members (name TEXT PRIMARY KEY, role TEXT);
            CREATE TABLE notes (id INTEGER PRIMARY KEY, member TEXT, text TEXT);
            INSERT INTO members VALUES ('Amira', 'reader'), ('Jonas', 'staff');
            INSERT INTO notes (member, text) VALUES ('Amira', 'Wants the next Butler')");
    }

    public function testOwnersAndStaffMayOpenANote(): void
    {
        self::assertTrue(mayOpen($this->db, 'Amira', 1));
        self::assertTrue(mayOpen($this->db, 'Jonas', 1));
    }

    public function testNobodyElseMay(): void
    {
        $this->db->exec("INSERT INTO members VALUES ('Kenji', 'reader')");

        self::assertFalse(mayOpen($this->db, 'Kenji', 1));
        self::assertFalse(mayOpen($this->db, 'Nobody', 1), 'Not a member');
        self::assertFalse(mayOpen($this->db, 'Amira', 99), 'No such note');
    }

    public function testAnErrorIsNeverAYes(): void
    {
        $this->db->exec('DROP TABLE members');

        $this->expectException(PDOException::class);

        mayOpen($this->db, 'Kenji', 1);
    }
}
