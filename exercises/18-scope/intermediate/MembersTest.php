<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MembersTest extends TestCase
{
    public function testAddsANewName(): void
    {
        require_once subject(__DIR__, 'members.php');

        self::assertSame(['Amira', 'Jonas'], withMember(['Amira'], 'Jonas'));
        self::assertSame(['Ana'], withMember([], 'Ana'));
    }

    public function testKeepsTheListWhenTheNameIsThere(): void
    {
        require_once subject(__DIR__, 'members.php');

        self::assertSame(['Amira', 'Jonas'], withMember(['Amira', 'Jonas'], 'Amira'));
    }

    public function testTheOriginalStaysAsItWas(): void
    {
        require_once subject(__DIR__, 'members.php');
        $members = ['Amira'];
        $new = withMember($members, 'Kenji');

        self::assertSame(['Amira'], $members);
        self::assertSame(['Amira', 'Kenji'], $new);
        self::assertSame('array', (string) (new ReflectionFunction('withMember'))->getReturnType());
    }
}
