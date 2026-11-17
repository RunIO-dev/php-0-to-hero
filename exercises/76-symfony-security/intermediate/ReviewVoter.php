<?php

declare(strict_types=1);

namespace Reviews;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Who may do what with a Review (next to this file):
 * - EDIT: only its author;
 * - DELETE: its author, or anyone with ROLE_STAFF (staff remove reviews that break the rules,
 *   but never change what someone wrote);
 * - a visitor who isn't logged in: nothing;
 * - any other attribute, or a subject that isn't a Review: not this voter's question (it abstains).
 *
 * @extends Voter<string, Review>
 */
final class ReviewVoter extends Voter
{
    public const string EDIT = 'EDIT';
    public const string DELETE = 'DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return true; // TODO
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $token->getUser() !== null; // TODO
    }
}
