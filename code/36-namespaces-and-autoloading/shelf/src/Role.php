<?php

declare(strict_types=1);

namespace Shelf;

/** What a member is to the library. The role decides how long a loan lasts. */
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
}
