<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Shelf\Doctrine\DoctrineMemberRepository;
use Shelf\Doctrine\DoctrineTokenRepository;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\Role;

final class DoctrineTokenRepositoryTest extends TestCase
{
    private EntityManager $entities;
    private DoctrineTokenRepository $tokens;

    protected function setUp(): void
    {
        $this->entities = Entities::manager();
        $members = new DoctrineMemberRepository($this->entities);
        $members->add(new Member('Amira', Role::Reader));
        $this->tokens = new DoctrineTokenRepository($this->entities, $members);
    }

    public function testATokenFindsItsMemberUntilItExpires(): void
    {
        $this->tokens->add('hash-1', 'amira', new DateTimeImmutable('2026-04-01 10:00 UTC'));

        self::assertSame('Amira', $this->tokens->memberName('hash-1', new DateTimeImmutable('2026-04-01 09:59 UTC')));
        self::assertNull($this->tokens->memberName('hash-1', new DateTimeImmutable('2026-04-01 10:00 UTC')));
        self::assertNull($this->tokens->memberName('hash-2', new DateTimeImmutable('2026-03-02 10:00 UTC')));
    }

    public function testARemovedTokenIsGone(): void
    {
        $this->tokens->add('hash-1', 'Amira', new DateTimeImmutable('2026-04-01 10:00 UTC'));

        $this->tokens->remove('hash-1');
        $this->entities->clear();

        self::assertNull($this->tokens->memberName('hash-1', new DateTimeImmutable('2026-03-02 10:00 UTC')));
    }

    public function testATokenGoesWithItsMember(): void
    {
        $this->tokens->add('hash-1', 'Amira', new DateTimeImmutable('2026-04-01 10:00 UTC'));

        $this->entities->getConnection()->executeStatement("DELETE FROM members WHERE name = 'Amira'");

        self::assertSame(0, $this->entities->getConnection()->fetchOne('SELECT COUNT(*) FROM api_tokens'));
    }

    public function testOnlyAMemberGetsAToken(): void
    {
        $this->expectException(MemberNotFound::class);

        $this->tokens->add('hash-1', 'Nobody', new DateTimeImmutable('2026-04-01 10:00 UTC'));
    }
}
