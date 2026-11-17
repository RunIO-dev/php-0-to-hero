<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';
require_once subject(__DIR__, 'members.php');

final class MembersTest extends TestCase
{
    private Members $members;

    protected function setUp(): void
    {
        $this->members = new Members(testLibrary());
    }

    public function testAnAddedMemberCanBeFound(): void
    {
        $id = $this->members->add('Amira', 'reader');

        self::assertSame(2, $id);
        self::assertSame(['name' => 'Amira', 'role' => 'reader'], $this->members->find($id));
        self::assertSame(['name' => 'Jonas', 'role' => 'staff'], $this->members->find(1));
    }

    public function testNobodyHasAnUnusedId(): void
    {
        self::assertNull($this->members->find(99));
    }

    public function testTheRoleCanChange(): void
    {
        $id = $this->members->add('Kenji', 'reader');

        $this->members->changeRole($id, 'staff');

        self::assertSame(['name' => 'Kenji', 'role' => 'staff'], $this->members->find($id));
    }

    public function testARemovedMemberIsGone(): void
    {
        $this->members->remove(1);

        self::assertNull($this->members->find(1));
    }

    public function testChangingSomeoneWhoIsntThereFails(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $this->members->changeRole(99, 'staff');
    }

    public function testRemovingSomeoneWhoIsntThereFails(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $this->members->remove(99);
    }
}
