<?php

declare(strict_types=1);

// Each test gets a new database in memory: no test sees what another one wrote.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/notes.php';

final class NotesTest extends TestCase
{
    private Notes $notes;

    protected function setUp(): void
    {
        $db = new PDO('sqlite::memory:');
        $db->exec(Notes::TABLE);
        $this->notes = new Notes($db);
    }

    public function testANoteIsKeptForItsMember(): void
    {
        $this->notes->add('Amira', 'Wants the next Butler');
        $this->notes->add('Jonas', 'Owes a fine');

        self::assertSame(['Wants the next Butler'], $this->notes->of('Amira'));
    }

    public function testNotesComeBackOldestFirst(): void
    {
        $this->notes->add('Amira', 'First');
        $this->notes->add('Amira', 'Second');

        self::assertSame(['First', 'Second'], $this->notes->of('Amira'));
    }

    public function testTheDatabaseRefusesAnEmptyNote(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('CHECK constraint failed');

        $this->notes->add('Amira', '   ');
    }
}
