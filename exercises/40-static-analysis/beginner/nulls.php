<?php

declare(strict_types=1);

// Exercise 40, beginner: a null that PHPStan finds.
//
// The script works for the members it looks for today. PHPStan at level 8 sees
// that memberOf() can return null, and strtoupper() needs a string:
//   vendor/bin/phpstan analyse --level 8 exercises/40-static-analysis/beginner/nulls.php
// Change only greeting(): for a title without a loan, it returns "Kindred: on the shelf".
// No casts such as (string): check for null.

/** Who has borrowed each title. */
const LOANS = ['Kindred' => 'Amira', 'Sula' => 'Jonas'];

function memberOf(string $title): ?string
{
    return LOANS[$title] ?? null;
}

function greeting(string $title): string
{
    return $title . ': with ' . strtoupper(memberOf($title));
}

echo greeting('Kindred'), "\n";
echo greeting('Sula'), "\n";

// Check your work: composer exercise 40 beginner
