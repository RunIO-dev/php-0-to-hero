<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Reviews\Review;
use Reviews\ReviewVoter;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

require_once subject(__DIR__, 'Review.php');
require_once subject(__DIR__, 'ReviewVoter.php');

final class ReviewVoterTest extends TestCase
{
    /** @return array<string, array{string, list<string>, string, int}> */
    public static function cases(): array
    {
        return [
            'the author edits' => ['Amira', ['ROLE_READER'], ReviewVoter::EDIT, VoterInterface::ACCESS_GRANTED],
            'the author deletes' => ['Amira', ['ROLE_READER'], ReviewVoter::DELETE, VoterInterface::ACCESS_GRANTED],
            'another reader edits' => ['Ana', ['ROLE_READER'], ReviewVoter::EDIT, VoterInterface::ACCESS_DENIED],
            'another reader deletes' => ['Ana', ['ROLE_READER'], ReviewVoter::DELETE, VoterInterface::ACCESS_DENIED],
            'staff delete' => ['Jonas', ['ROLE_STAFF'], ReviewVoter::DELETE, VoterInterface::ACCESS_GRANTED],
            'staff never edit' => ['Jonas', ['ROLE_STAFF'], ReviewVoter::EDIT, VoterInterface::ACCESS_DENIED],
            'another question' => ['Amira', ['ROLE_READER'], 'PUBLISH', VoterInterface::ACCESS_ABSTAIN],
        ];
    }

    /** @param list<string> $roles */
    #[DataProvider('cases')]
    public function testWhoMayDoWhat(string $name, array $roles, string $attribute, int $expected): void
    {
        $token = new UsernamePasswordToken(new InMemoryUser($name, null, $roles), 'main', $roles);

        self::assertSame($expected, new ReviewVoter()->vote($token, self::review(), [$attribute]));
    }

    public function testAVisitorMayDoNothing(): void
    {
        self::assertSame(VoterInterface::ACCESS_DENIED, new ReviewVoter()->vote(new NullToken(), self::review(), [ReviewVoter::DELETE]));
    }

    public function testOnlyReviews(): void
    {
        $token = new UsernamePasswordToken(new InMemoryUser('Amira', null, ['ROLE_READER']), 'main', ['ROLE_READER']);

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, new ReviewVoter()->vote($token, 'Kindred', [ReviewVoter::EDIT]));
    }

    private static function review(): Review
    {
        return new Review('Amira', 'Kindred', 'Read it in one night.');
    }
}
