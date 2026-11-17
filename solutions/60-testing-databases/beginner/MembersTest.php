<?php

declare(strict_types=1);

// Exercise 60, beginner: tests for a class that uses a database.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/members.php';

final class MembersTest extends TestCase
{
    private Members $members;

    protected function setUp(): void
    {
        $db = new PDO('sqlite::memory:');
        $db->exec(Members::TABLE);
        $this->members = new Members($db);
    }

    public function testAnAddedMemberCanBeFound(): void
    {
        $amira = $this->members->add('Amira', 'reader');
        $jonas = $this->members->add('Jonas', 'staff');

        self::assertSame(['name' => 'Amira', 'role' => 'reader'], $this->members->find($amira));
        self::assertSame(['name' => 'Jonas', 'role' => 'staff'], $this->members->find($jonas));
    }

    public function testAnUnknownIdFindsNobody(): void
    {
        $this->members->add('Amira', 'reader');

        self::assertNull($this->members->find(99));
    }

    public function testTheRoleCanChange(): void
    {
        $id = $this->members->add('Kenji', 'reader');

        $this->members->changeRole($id, 'staff');

        self::assertSame(['name' => 'Kenji', 'role' => 'staff'], $this->members->find($id));
    }

    public function testChangingAnUnknownMemberFails(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $this->members->changeRole(99, 'staff');
    }

    public function testOnlyTheRemovedMemberIsGone(): void
    {
        $amira = $this->members->add('Amira', 'reader');
        $jonas = $this->members->add('Jonas', 'staff');

        $this->members->remove($amira);

        self::assertNull($this->members->find($amira));
        self::assertNotNull($this->members->find($jonas));
    }

    public function testRemovingAnUnknownMemberFails(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $this->members->remove(99);
    }
}
