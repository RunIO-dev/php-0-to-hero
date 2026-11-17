<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';
require_once subject(__DIR__, 'add.php');

final class AddTest extends TestCase
{
    public function testTheNewBookIsStoredWithItsNewId(): void
    {
        $db = testLibrary();

        $id = addBook($db, "Midnight's Children", 'Salman Rushdie', 1981, 2);

        self::assertSame(6, $id);
        $row = $db->query('SELECT title, author, year, copies FROM books WHERE id = 6');
        self::assertSame(
            ['title' => "Midnight's Children", 'author' => 'Salman Rushdie', 'year' => 1981, 'copies' => 2],
            $row === false ? null : $row->fetch(),
        );
    }

    public function testOneCopyUnlessSaidOtherwise(): void
    {
        $db = testLibrary();

        addBook($db, 'Kitchen', 'Banana Yoshimoto', 1988);

        $copies = $db->query("SELECT copies FROM books WHERE title = 'Kitchen'");
        self::assertSame(1, $copies === false ? null : $copies->fetchColumn());
    }

    public function testATitleThatIsThereAlreadyIsRefused(): void
    {
        $this->expectException(PDOException::class);

        addBook(testLibrary(), 'Sula', 'Toni Morrison', 1973);
    }
}
