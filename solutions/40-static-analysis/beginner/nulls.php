<?php

declare(strict_types=1);

// Exercise 40, beginner: a null that PHPStan finds. A solution.

/** Who has borrowed each title. */
const LOANS = ['Kindred' => 'Amira', 'Sula' => 'Jonas'];

function memberOf(string $title): ?string
{
    return LOANS[$title] ?? null;
}

function greeting(string $title): string
{
    $member = memberOf($title);

    return $member === null ? "$title: on the shelf" : $title . ': with ' . strtoupper($member);
}

echo greeting('Kindred'), "\n";
echo greeting('Sula'), "\n";
