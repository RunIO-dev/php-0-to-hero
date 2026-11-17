<?php

declare(strict_types=1);

// Exercise 61, beginner: store passwords safely.

function storable(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function matches(string $typed, string $stored): bool
{
    return password_verify($typed, $stored);
}
