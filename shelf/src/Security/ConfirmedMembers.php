<?php

declare(strict_types=1);

namespace Shelf\Security;

use Shelf\Member;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Who may log in (chapter 82): a member who signed up, only after using the link in the first email.
 * Asked after the password was checked: only someone who knows it learns that the account waits.
 */
final readonly class ConfirmedMembers implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void {}

    public function checkPostAuth(UserInterface $user): void
    {
        if ($user instanceof Member && !$user->confirmed) {
            throw new CustomUserMessageAccountStatusException('Please confirm your email first: the link is in the message Shelf sent you.');
        }
    }
}
