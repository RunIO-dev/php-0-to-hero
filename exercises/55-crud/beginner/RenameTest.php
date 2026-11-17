<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';
require_once subject(__DIR__, 'rename.php');

final class RenameTest extends TestCase
{
    /** @return list<string> */
    private function titles(PDO $db): array
    {
        $titles = $db->query('SELECT title FROM books ORDER BY id');

        return $titles === false ? [] : array_column($titles->fetchAll(), 'title');
    }

    public function testTheTitleIsCorrected(): void
    {
        $db = testLibrary();

        self::assertTrue(renameBook($db, 'Kindred', 'Kindred: A Novel'));
        self::assertSame(['Things Fall Apart', 'Kindred: A Novel', 'Sula', 'Beloved', 'At Swim-Two-Birds'], $this->titles($db));
    }

    public function testAnUnknownTitleChangesNothing(): void
    {
        $db = testLibrary();

        self::assertFalse(renameBook($db, 'Dune', 'Dune Messiah'));
        self::assertSame(['Things Fall Apart', 'Kindred', 'Sula', 'Beloved', 'At Swim-Two-Birds'], $this->titles($db));
    }

    public function testATitleThatIsTakenIsRefused(): void
    {
        $this->expectException(PDOException::class);

        renameBook(testLibrary(), 'Beloved', 'Sula');
    }
}
