<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MembersTest extends TestCase
{
    public function testAllAndFind(): void
    {
        require_once subject(__DIR__, 'members.php');

        $amira = new Member('Amira');
        $jonas = new Member('Jonas');
        $repository = new InMemoryMemberRepository([$amira, $jonas]);

        self::assertInstanceOf(MemberRepository::class, $repository);
        self::assertSame([$amira, $jonas], $repository->all());
        self::assertSame($jonas, $repository->find(' jonas '));
        self::assertSame($amira, $repository->find('AMIRA'));
        self::assertNull($repository->find('Zoe'));
    }

    public function testEmpty(): void
    {
        require_once subject(__DIR__, 'members.php');

        $repository = new InMemoryMemberRepository([]);

        self::assertSame([], $repository->all());
        self::assertNull($repository->find('Amira'));
    }
}
