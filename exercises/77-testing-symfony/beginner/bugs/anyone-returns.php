<?php

declare(strict_types=1);

// Bug: any member may return any loan.

// A loan, and who may do what with it, as a voter (chapter 76).

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class Loan
{
    public function __construct(public readonly string $member, public readonly string $title, public bool $extended = false) {}
}

/**
 * - RETURN: the member who borrowed the book, or anyone with ROLE_STAFF (at the desk);
 * - EXTEND: only the member who borrowed it, and only once (not when $loan->extended);
 * - a visitor who isn't logged in: nothing;
 * - another attribute, or a subject that isn't a Loan: the voter abstains.
 *
 * @extends Voter<string, Loan>
 */
final class LoanVoter extends Voter
{
    public const string RETURN = 'RETURN';
    public const string EXTEND = 'EXTEND';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::RETURN, self::EXTEND], true) && $subject instanceof Loan;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if ($user === null) {
            return false;
        }
        $own = $user->getUserIdentifier() === $subject->member;

        return match ($attribute) {
            self::RETURN => true,
            self::EXTEND => $own && !$subject->extended,
            default => false,
        };
    }
}
