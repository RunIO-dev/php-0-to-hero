<?php

declare(strict_types=1);

// A database server can't be made new for each test quickly. Instead, each test runs in a
// transaction that is rolled back afterwards, so it leaves nothing behind.
// Start MariaDB first: docker compose up -d --wait db

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/notes.php';

final class RollbackTest extends TestCase
{
    private static PDO $db;
    private Notes $notes;

    public static function setUpBeforeClass(): void
    {
        self::$db = new PDO('mysql:host=db;dbname=shelf;charset=utf8mb4', 'shelf_admin', (string) getenv('SHELF_ADMIN_PASSWORD'));
        self::$db->exec('DROP TABLE IF EXISTS notes'); // a change to tables ends any transaction, so it comes first
        self::$db->exec(str_replace('INTEGER PRIMARY KEY', 'INT AUTO_INCREMENT PRIMARY KEY', Notes::TABLE));
    }

    protected function setUp(): void
    {
        self::$db->beginTransaction();
        $this->notes = new Notes(self::$db);
    }

    protected function tearDown(): void
    {
        self::$db->rollBack();
    }

    public function testANoteIsKept(): void
    {
        $this->notes->add('Amira', 'Wants the next Butler');

        self::assertSame(['Wants the next Butler'], $this->notes->of('Amira'));
    }

    public function testTheNoteOfTheTestBeforeIsGone(): void
    {
        $this->notes->add('Amira', 'Owes a fine');

        self::assertSame(['Owes a fine'], $this->notes->of('Amira'));
    }
}
