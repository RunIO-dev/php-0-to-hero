<?php

declare(strict_types=1);

// Exercise 42, beginner: testing exceptions. A solution.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/member.php';

final class MemberTest extends TestCase
{
    public function testAWelcome(): void
    {
        self::assertSame('Welcome, Amira!', newMember('Amira', 30));
    }

    public function testSixYearOldsMayJoin(): void
    {
        self::assertSame('Welcome, Kenji!', newMember(' Kenji ', 6));
    }

    public function testFiveYearOldsAreTooYoung(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kenji is too young: members are at least 6 years old');

        newMember('Kenji', 5);
    }

    public function testANameIsRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A member needs a name');

        newMember('   ', 30);
    }
}
