<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'login.php');

final class LoginTest extends TestCase
{
    private const string PASSWORD = 'tea kettle under the lemon tree';

    private PDO $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec('CREATE TABLE members (name TEXT PRIMARY KEY, password_hash TEXT)');
        $add = $this->db->prepare('INSERT INTO members (name, password_hash) VALUES (:name, :hash)');
        $add->execute(['name' => 'Amira', 'hash' => password_hash(self::PASSWORD, PASSWORD_BCRYPT, ['cost' => 10])]);
        $add->execute(['name' => 'Jonas', 'hash' => null]);
    }

    public function testTheRightPasswordLogsIn(): void
    {
        self::assertTrue(logIn($this->db, 'Amira', self::PASSWORD));
    }

    public function testNobodyElseLogsIn(): void
    {
        self::assertFalse(logIn($this->db, 'Amira', 'Tea kettle under the lemon tree'));
        self::assertFalse(logIn($this->db, 'Jonas', ''), 'Jonas has no password');
        self::assertFalse(logIn($this->db, 'Nobody', self::PASSWORD));
        self::assertFalse(logIn($this->db, "Amira' --", self::PASSWORD));
    }

    public function testAnOldHashIsReplacedAfterALogin(): void
    {
        $before = $this->hash();
        logIn($this->db, 'Amira', 'wrong password, nothing changes');
        self::assertSame($before, $this->hash());

        logIn($this->db, 'Amira', self::PASSWORD);

        self::assertFalse(password_needs_rehash($this->hash(), PASSWORD_DEFAULT), 'Store a new hash with PASSWORD_DEFAULT');
        self::assertTrue(password_verify(self::PASSWORD, $this->hash()));
    }

    private function hash(): string
    {
        $hash = $this->db->query("SELECT password_hash FROM members WHERE name = 'Amira'");

        return $hash === false ? '' : (string) $hash->fetchColumn();
    }
}
