<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MemberTest extends TestCase
{
    public function testAValidMember(): void
    {
        require_once subject(__DIR__, 'member.php');

        $member = new Member('  Amira ');
        self::assertSame('Amira', $member->name);
        self::assertSame(3, $member->maxLoans);

        self::assertSame(10, (new Member('Jonas', 10))->maxLoans);
        self::assertSame(1, (new Member('Kenji', maxLoans: 1))->maxLoans);
    }

    /** @return array<string, array{string, int}> */
    public static function invalid(): array
    {
        return [
            'empty name' => ['', 3],
            'only spaces' => ['   ', 3],
            'no loans' => ['Ana', 0],
            'too many' => ['Ana', 11],
            'negative' => ['Ana', -1],
        ];
    }

    #[DataProvider('invalid')]
    public function testInvalidMembersAreRefused(string $name, int $maxLoans): void
    {
        require_once subject(__DIR__, 'member.php');

        $this->expectException(InvalidArgumentException::class);
        new Member($name, $maxLoans);
    }
}
