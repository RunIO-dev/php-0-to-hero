<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use PDOException;
use PHPUnit\Framework\TestCase;
use Shelf\Member;
use Shelf\PdoMemberRepository;
use Shelf\Role;

use function Shelf\openDatabase;

final class PdoMemberRepositoryTest extends TestCase
{
    private PdoMemberRepository $members;

    protected function setUp(): void
    {
        $this->members = new PdoMemberRepository(openDatabase(':memory:'));
        $this->members->add(new Member('Amira', Role::Reader));
    }

    public function testAMemberCanBeFoundInAnyCase(): void
    {
        self::assertEquals(new Member('Amira', Role::Reader), $this->members->find(' amira '));
        self::assertNull($this->members->find('Nobody'));
    }

    public function testTheDatabaseRefusesANameTwice(): void
    {
        $this->expectException(PDOException::class);

        $this->members->add(new Member('AMIRA', Role::Staff));
    }
}
