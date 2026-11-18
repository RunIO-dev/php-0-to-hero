<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Desk.php');

final class DeskTest extends TestCase
{
    private PDO $db;
    private Desk $desk;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec('CREATE TABLE books (title TEXT PRIMARY KEY, copies INTEGER NOT NULL)');
        $this->db->exec('CREATE TABLE loans (id INTEGER PRIMARY KEY, title TEXT, member TEXT, returned INTEGER NOT NULL DEFAULT 0)');
        $this->db->exec("INSERT INTO books VALUES ('Kindred', 1), ('Sula', 5)");
        $this->desk = new Desk($this->db);
    }

    public function testALoanTakesACopyAndAReturnPutsItBack(): void
    {
        $id = $this->desk->lend('Kindred', 'Amira');

        self::assertSame([0, 1], [$this->copies('Kindred'), $this->desk->out('Amira')]);
        $this->desk->giveBack($id);
        self::assertSame([1, 0], [$this->copies('Kindred'), $this->desk->out('Amira')]);
    }

    public function testTheLastCopyGoesOnce(): void
    {
        $this->desk->lend('Kindred', 'Amira');

        try {
            $this->desk->lend('Kindred', 'Kenji');
            self::fail('Lent a copy that was not there');
        } catch (DeskError) {
            self::assertSame([0, 0], [$this->copies('Kindred'), $this->desk->out('Kenji')]);
        }
    }

    public function testAMemberHasThreeBooksAtMost(): void
    {
        foreach (range(1, 3) as $n) {
            $this->desk->lend('Sula', 'Amira');
        }

        try {
            $this->desk->lend('Sula', 'Amira');
            self::fail('Lent a fourth book');
        } catch (DeskError) {
            self::assertSame(2, $this->copies('Sula'), 'Nothing changed');
        }
    }

    public function testABookComesBackOnce(): void
    {
        $id = $this->desk->lend('Kindred', 'Amira');
        $this->desk->giveBack($id);

        foreach ([$id, 99] as $wrong) {
            try {
                $this->desk->giveBack($wrong);
                self::fail("Loan $wrong came back");
            } catch (DeskError) {
                self::assertSame(1, $this->copies('Kindred'), 'No copy was added');
            }
        }
    }

    public function testNoTransactionIsLeftOpen(): void
    {
        $this->desk->lend('Kindred', 'Amira');
        try {
            $this->desk->lend('Kindred', 'Kenji');
        } catch (DeskError) {
        }

        self::assertFalse($this->db->inTransaction());
    }

    private function copies(string $title): int
    {
        $copies = $this->db->prepare('SELECT copies FROM books WHERE title = ?');
        $copies->execute([$title]);

        return (int) $copies->fetchColumn();
    }
}
