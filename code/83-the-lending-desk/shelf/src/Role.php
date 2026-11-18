<?php

declare(strict_types=1);

namespace Shelf;

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
