<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\TestCase;
use Shelf\Doctrine\DoctrineMemberRepository;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\Role;

final class DoctrineMemberRepositoryTest extends TestCase
{
    private DoctrineMemberRepository $members;

    protected function setUp(): void
    {
        $this->members = new DoctrineMemberRepository(Entities::manager());
        $this->members->add(new Member('Amira', Role::Reader));
    }

    public function testAMemberCanBeFoundInAnyCase(): void
    {
        $amira = $this->members->find(' amira ');

        self::assertSame(['Amira', Role::Reader], [$amira?->name, $amira?->role]);
        self::assertNull($this->members->find('Nobody'));
    }

    public function testTheDatabaseRefusesANameTwice(): void
    {
        $this->expectException(UniqueConstraintViolationException::class);

        $this->members->add(new Member('AMIRA', Role::Staff));
    }

    public function testAPasswordHashIsStoredForItsMember(): void
    {
        self::assertNull($this->members->passwordHash('Amira'), 'A new member has no password');

        $this->members->changePasswordHash('amira', '$2y$04$hash');

        self::assertSame('$2y$04$hash', $this->members->passwordHash('Amira'));
        self::assertNull($this->members->passwordHash('Nobody'));
    }

    public function testOnlyAMemberGetsAPasswordHash(): void
    {
        $this->expectException(MemberNotFound::class);

        $this->members->changePasswordHash('Nobody', '$2y$04$hash');
    }
}
