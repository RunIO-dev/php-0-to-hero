<?php

declare(strict_types=1);

// Exercise 77, beginner: tests for a voter.

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

require_once __DIR__ . '/loans.php';

final class LoanVoterTest extends TestCase
{
    private const int YES = VoterInterface::ACCESS_GRANTED;
    private const int NO = VoterInterface::ACCESS_DENIED;

    /** @return iterable<string, array{string, string, bool, string, int}> who, their role, extended?, what, the answer */
    public static function cases(): iterable
    {
        yield 'the borrower returns' => ['Amira', 'ROLE_READER', false, LoanVoter::RETURN, self::YES];
        yield 'staff return at the desk' => ['Jonas', 'ROLE_STAFF', false, LoanVoter::RETURN, self::YES];
        yield 'another reader returns' => ['Ana', 'ROLE_READER', false, LoanVoter::RETURN, self::NO];
        yield 'the borrower extends' => ['Amira', 'ROLE_READER', false, LoanVoter::EXTEND, self::YES];
        yield 'the borrower extends again' => ['Amira', 'ROLE_READER', true, LoanVoter::EXTEND, self::NO];
        yield 'staff extend' => ['Jonas', 'ROLE_STAFF', false, LoanVoter::EXTEND, self::NO];
        yield 'another reader extends' => ['Ana', 'ROLE_READER', false, LoanVoter::EXTEND, self::NO];
    }

    #[DataProvider('cases')]
    public function testWhoMayDoWhat(string $name, string $role, bool $extended, string $attribute, int $answer): void
    {
        self::assertSame($answer, self::vote(self::member($name, $role), new Loan('Amira', 'Kindred', $extended), $attribute));
    }

    public function testAVisitorMayDoNothing(): void
    {
        foreach ([LoanVoter::RETURN, LoanVoter::EXTEND] as $attribute) {
            self::assertSame(self::NO, self::vote(new NullToken(), new Loan('Amira', 'Kindred'), $attribute), $attribute);
        }
    }

    public function testOtherQuestionsAreLeftToOtherVoters(): void
    {
        $amira = self::member('Amira', 'ROLE_READER');

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, self::vote($amira, new Loan('Amira', 'Kindred'), 'DELETE'));
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, self::vote($amira, 'Kindred', LoanVoter::RETURN));
    }

    private static function member(string $name, string $role): TokenInterface
    {
        return new UsernamePasswordToken(new InMemoryUser($name, null, [$role]), 'main', [$role]);
    }

    private static function vote(TokenInterface $token, mixed $subject, string $attribute): int
    {
        return new LoanVoter()->vote($token, $subject, [$attribute]);
    }
}
