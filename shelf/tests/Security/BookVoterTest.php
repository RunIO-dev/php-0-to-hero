<?php

declare(strict_types=1);

namespace Shelf\Tests\Security;

use PHPUnit\Framework\TestCase;
use Shelf\Member;
use Shelf\Role;
use Shelf\Security\BookVoter;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/** Who may change books (chapter 76): the voter, asked as Symfony asks it, with a logged-in member's token. */
final class BookVoterTest extends TestCase
{
    public function testStaffMayChangeBooks(): void
    {
        self::assertSame(VoterInterface::ACCESS_GRANTED, self::vote(new Member('Jonas', Role::Staff)));
    }

    public function testReadersMayNot(): void
    {
        self::assertSame(VoterInterface::ACCESS_DENIED, self::vote(new Member('Amira', Role::Reader)));
    }

    public function testNorMayAVisitor(): void
    {
        self::assertSame(VoterInterface::ACCESS_DENIED, new BookVoter()->vote(new NullToken(), null, [BookVoter::CHANGE]));
    }

    public function testOtherQuestionsAreNotItsBusiness(): void
    {
        $token = new UsernamePasswordToken(new Member('Jonas', Role::Staff), 'main', ['ROLE_STAFF']);

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, new BookVoter()->vote($token, null, ['ROLE_STAFF']));
    }

    private static function vote(Member $member): int
    {
        return new BookVoter()->vote(new UsernamePasswordToken($member, 'main', $member->getRoles()), null, [BookVoter::CHANGE]);
    }
}
