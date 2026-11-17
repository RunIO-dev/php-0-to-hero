<?php

declare(strict_types=1);

// Roles as plain strings: a typo is just another string.
function loanDays(string $role): int
{
    return match ($role) {
        'staff' => 28,
        default => 14,
    };
}

echo loanDays('staff'), "\n";
echo loanDays('Staff'), "\n";
echo loanDays('stafff'), "\n";
