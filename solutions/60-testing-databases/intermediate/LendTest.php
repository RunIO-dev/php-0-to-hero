<?php

declare(strict_types=1);

// Exercise 60, intermediate: tests for a transaction.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/lend.php';

final class LendTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->exec(LIBRARY_TABLES);
        $this->db->exec("INSERT INTO books (title, copies) VALUES ('Kindred', 1), ('Sula', 5)");
        $this->db->exec("INSERT INTO members (name) VALUES ('Amira'), ('Jonas')");
    }

    private function copiesOf(string $title): mixed
    {
        $copies = $this->db->prepare('SELECT copies FROM books WHERE title = :title');
        $copies->execute(['title' => $title]);

        return $copies->fetchColumn();
    }

    private function loans(): mixed
    {
        $loans = $this->db->query('SELECT COUNT(*) FROM loans');

        return $loans === false ? null : $loans->fetchColumn();
    }

    public function testALoanTakesACopy(): void
    {
        self::assertSame(1, lend($this->db, 'Kindred', 'Amira', '2026-03-16'));
        self::assertSame(0, $this->copiesOf('Kindred'));
        self::assertSame(1, $this->loans());
    }

    public function testTheLastCopyGoesOnce(): void
    {
        lend($this->db, 'Kindred', 'Amira', '2026-03-16');

        try {
            lend($this->db, 'Kindred', 'Jonas', '2026-03-16');
            self::fail('The last copy was lent twice');
        } catch (DomainException) {
        }
        self::assertSame(0, $this->copiesOf('Kindred'));
        self::assertSame(1, $this->loans());
    }

    public function testThreeBooksAtMost(): void
    {
        for ($i = 0; $i < 3; $i++) {
            lend($this->db, 'Sula', 'Amira', '2026-03-16');
        }

        $this->expectException(DomainException::class);

        lend($this->db, 'Sula', 'Amira', '2026-03-16');
    }

    public function testAnUnknownMemberChangesNothing(): void
    {
        try {
            lend($this->db, 'Kindred', 'Nobody', '2026-03-16');
            self::fail('An unknown member must be refused');
        } catch (OutOfBoundsException) {
        }
        self::assertSame(1, $this->copiesOf('Kindred'));
        self::assertSame(0, $this->loans());
    }

    public function testLendingWorksAfterARefusal(): void
    {
        try {
            lend($this->db, 'Dune', 'Amira', '2026-03-16');
        } catch (OutOfBoundsException) {
        }

        self::assertSame(1, lend($this->db, 'Sula', 'Amira', '2026-03-16'));
    }
}
