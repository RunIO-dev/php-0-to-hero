<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';

final class RebuildTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        $this->db = testLibrary();
        migrate($this->db, subject(__DIR__, '005-book-rules.sql'));
    }

    public function testEveryBookIsThereWithItsId(): void
    {
        self::assertSame([
            ['id' => 1, 'title' => 'Things Fall Apart', 'copies' => 2],
            ['id' => 3, 'title' => 'Kindred', 'copies' => 0],
            ['id' => 4, 'title' => 'Sula', 'copies' => 1],
        ], rowsOf($this->db, 'SELECT id, title, copies FROM books ORDER BY id'));
    }

    public function testTheLoansStillPointToTheirBooks(): void
    {
        self::assertSame(
            [['title' => 'Kindred', 'name' => 'Amira'], ['title' => 'Things Fall Apart', 'name' => 'Jonas']],
            rowsOf($this->db, 'SELECT books.title, members.name FROM loans
                JOIN books ON books.id = loans.book_id JOIN members ON members.id = loans.member_id ORDER BY loans.id'),
        );
        self::assertSame([], rowsOf($this->db, 'PRAGMA foreign_key_check'));
    }

    public function testNegativeCopiesAreRefused(): void
    {
        $this->expectException(PDOException::class);

        $this->db->exec('UPDATE books SET copies = -1 WHERE id = 4');
    }

    public function testATitleInOtherLettersIsRefused(): void
    {
        $this->expectException(PDOException::class);

        $this->db->exec("INSERT INTO books (title, copies) VALUES ('SULA', 1)");
    }

    public function testTextIsRefusedAsCopies(): void
    {
        $this->expectException(PDOException::class);

        $this->db->exec("INSERT INTO books (title, copies) VALUES ('Beloved', 'two')");
    }
}
