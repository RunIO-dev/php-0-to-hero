<?php

declare(strict_types=1);

// One database for every test: the second test sees the first one's note.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/notes.php';

final class SharedDatabaseTest extends TestCase
{
    private static ?Notes $notes = null;

    private function notes(): Notes
    {
        if (self::$notes === null) {
            $db = new PDO('sqlite::memory:');
            $db->exec(Notes::TABLE);
            self::$notes = new Notes($db);
        }

        return self::$notes;
    }

    public function testANoteIsKept(): void
    {
        $this->notes()->add('Amira', 'Wants the next Butler');

        self::assertSame(['Wants the next Butler'], $this->notes()->of('Amira'));
    }

    public function testTheNoteFromBeforeIsNotThere(): void
    {
        $this->notes()->add('Amira', 'Owes a fine');

        self::assertSame(['Owes a fine'], $this->notes()->of('Amira'));
    }
}
