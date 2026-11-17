<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\PdoMemberRepository;
use Shelf\PdoTokenRepository;
use Shelf\Role;

use function Shelf\openDatabase;

final class PdoTokenRepositoryTest extends TestCase
{
    private PDO $db;
    private PdoTokenRepository $tokens;

    protected function setUp(): void
    {
        $this->db = openDatabase(':memory:');
        new PdoMemberRepository($this->db)->add(new Member('Amira', Role::Reader));
        $this->tokens = new PdoTokenRepository($this->db);
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

        self::assertNull($this->tokens->memberName('hash-1', new DateTimeImmutable('2026-03-02 10:00 UTC')));
    }

    public function testATokenGoesWithItsMember(): void
    {
        $this->tokens->add('hash-1', 'Amira', new DateTimeImmutable('2026-04-01 10:00 UTC'));

        $this->db->exec("DELETE FROM members WHERE name = 'Amira'");

        $count = $this->db->query('SELECT COUNT(*) FROM api_tokens');
        self::assertNotFalse($count);
        self::assertSame(0, $count->fetchColumn());
    }

    public function testOnlyAMemberGetsAToken(): void
    {
        $this->expectException(MemberNotFound::class);

        $this->tokens->add('hash-1', 'Nobody', new DateTimeImmutable('2026-04-01 10:00 UTC'));
    }
}
