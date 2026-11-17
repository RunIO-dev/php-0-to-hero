<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MemberTest extends TestCase
{
    public function testBorrowAndGiveBack(): void
    {
        require_once subject(__DIR__, 'member.php');

        $member = new Member('Amira');
        $member->borrow('Kindred');
        $member->borrow('Kitchen');
        $member->borrow('Sula');
        $member->giveBack('Kitchen');

        self::assertSame('Amira', $member->name);
        self::assertSame(['Kindred', 'Sula'], $member->titles());
    }

    public function testTheListStaysPrivate(): void
    {
        require_once subject(__DIR__, 'member.php');

        $member = new Member('Jonas');
        $member->borrow('Kindred');
        $titles = $member->titles();
        $titles[] = 'Beloved';

        self::assertSame(['Kindred'], $member->titles());
        self::assertFalse((new ReflectionProperty(Member::class, 'titles'))->isPublic(), 'Keep the titles private.');
    }

    public function testAFourthBook(): void
    {
        require_once subject(__DIR__, 'member.php');

        $member = new Member('Kenji');
        foreach (['Kindred', 'Kitchen', 'Sula'] as $title) {
            $member->borrow($title);
        }
        $this->expectException(RuntimeException::class);
        $member->borrow('Beloved');
    }

    public function testGivingBackWhatIsNotBorrowed(): void
    {
        require_once subject(__DIR__, 'member.php');

        $member = new Member('Ana');
        $member->borrow('Kindred');
        $this->expectException(RuntimeException::class);
        $member->giveBack('Kitchen');
    }
}
