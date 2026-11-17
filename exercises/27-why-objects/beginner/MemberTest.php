<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MemberTest extends TestCase
{
    public function testANewMemberHasNoLoans(): void
    {
        require_once subject(__DIR__, 'member.php');

        $member = new Member();
        $member->name = 'Amira';

        self::assertSame('Amira', $member->name);
        self::assertSame(0, $member->loans);
        self::assertTrue($member->canBorrow());
    }

    public function testThreeLoansAreTheLimit(): void
    {
        require_once subject(__DIR__, 'member.php');

        $member = new Member();
        $member->loans = 2;
        self::assertTrue($member->canBorrow());

        $member->loans = 3;
        self::assertFalse($member->canBorrow());
    }
}
