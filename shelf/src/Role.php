<?php

declare(strict_types=1);

namespace Shelf;

use Symfony\Component\Translation\TranslatableMessage;

/**
 * What a member is to the library. The role decides how long a loan lasts, and what the member may
 * do on the website (chapter 63).
 */
enum Role: string
{
    case Reader = 'reader';
    case Staff = 'staff';

    public function loanDays(): int
    {
        return match ($this) {
            Role::Reader => 14,
            Role::Staff => 28,
        };
    }

    /** What the pages call the role (chapter 87), in their language. */
    public function label(): TranslatableMessage
    {
        return match ($this) {
            Role::Reader => new TranslatableMessage('Reader'),
            Role::Staff => new TranslatableMessage('Staff'),
        };
    }

    /** How many books a member may have at once (chapter 83). */
    public function maxLoans(): int
    {
        return match ($this) {
            Role::Reader => 5,
            Role::Staff => 10,
        };
    }

    /** Adding, changing and removing books: the library's staff only. */
    public function mayChangeBooks(): bool
    {
        return $this === Role::Staff;
    }
}
