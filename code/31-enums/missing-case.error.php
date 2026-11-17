<?php

declare(strict_types=1);

enum Role: string
{
    case Reader = 'reader';
    case Staff = 'staff';
    case Guest = 'guest';
}

function loanDays(Role $role): int
{
    return match ($role) {
        Role::Reader => 14,
        Role::Staff => 28,
    };
}

echo loanDays(Role::Guest), "\n";
