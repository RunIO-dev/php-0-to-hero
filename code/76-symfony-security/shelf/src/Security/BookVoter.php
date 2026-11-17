<?php

declare(strict_types=1);

namespace Shelf\Security;

use Shelf\Member;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Who may add, change and remove books (chapter 76): a voter, which Symfony asks whenever code checks
 * isGranted(BookVoter::CHANGE), in a controller, a template or #[IsGranted]. The answer comes from
 * Role::mayChangeBooks(), as before: the rule stays in one place.
 *
 * @extends Voter<string, mixed>
 */
final class BookVoter extends Voter
{
    public const string CHANGE = 'CHANGE_BOOKS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::CHANGE;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $member = $token->getUser();

        return $member instanceof Member && $member->role->mayChangeBooks();
    }
}
