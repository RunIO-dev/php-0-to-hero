<?php

declare(strict_types=1);

// Exercise 17, intermediate: named arguments.
//
// Call loanSlip() twice and print each slip on its own line:
//   Amira borrows Kindred for 14 days (can be renewed)
//   Jonas borrows Kitchen for 7 days (cannot be renewed)
// Use the default values where they fit, and named arguments for the rest,
// so that a reader sees what 7 and false mean.
//
// Check your work: composer exercise 17 intermediate

function loanSlip(string $member, string $title, int $days = 14, bool $renewable = true): string
{
    $renew = $renewable ? 'can be renewed' : 'cannot be renewed';

    return "$member borrows $title for $days days ($renew)";
}
