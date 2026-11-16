<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FindMemberTest extends TestCase
{
    private const MEMBERS = [
        ['name' => 'Amira', 'email' => 'amira@example.org'],
        ['name' => 'Jonas', 'email' => 'jonas@example.org'],
    ];

    public function testFindsTheMember(): void
    {
        require_once subject(__DIR__, 'find-member.php');

        self::assertSame(self::MEMBERS[1], findMember(self::MEMBERS, 'jonas@example.org'));
        self::assertSame(self::MEMBERS[0], findMember(self::MEMBERS, 'AMIRA@example.org'));
    }

    public function testThrowsForAnUnknownEmail(): void
    {
        require_once subject(__DIR__, 'find-member.php');

        $this->expectException(MemberNotFound::class);
        $this->expectExceptionMessage('No member with the email sula@example.org');
        findMember(self::MEMBERS, 'sula@example.org');
    }
}
