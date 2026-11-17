<?php

declare(strict_types=1);

enum Role: string
{
    case Reader = 'reader';
    case Staff = 'staff';

    // The rule for each case lives with the cases.
    public function loanDays(): int
    {
        return match ($this) {
            Role::Reader => 14,
            Role::Staff => 28,
        };
    }
}

foreach (Role::cases() as $role) {
    echo "$role->name: {$role->loanDays()} days\n";
}
