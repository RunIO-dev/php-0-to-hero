<?php

declare(strict_types=1);

// Exercise 61, beginner: store passwords safely.
//
// - storable($password) returns what goes into the database for a new password: a hash made by
//   password_hash, with PHP's default algorithm.
// - matches($typed, $stored) says whether a password someone typed belongs to a stored hash.

function storable(string $password): string
{
    return $password;
}

function matches(string $typed, string $stored): bool
{
    return $typed === $stored;
}
// Check your work: composer exercise 61 beginner
