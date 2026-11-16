<?php

declare(strict_types=1);

function loanSlip(string $member, string $title, int $days = 14, bool $renewable = true): string
{
    $renew = $renewable ? 'can be renewed' : 'cannot be renewed';

    return "$member borrows $title for $days days ($renew)";
}

echo loanSlip('Amira', 'Kindred'), "\n";
echo loanSlip('Jonas', 'Kitchen', days: 7, renewable: false), "\n";
