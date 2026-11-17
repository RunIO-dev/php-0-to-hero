<?php

declare(strict_types=1);

// Exercise 61, intermediate: rules for a new password.

const COMMON = ['password1234567', '123456789012345', 'qwertyuiopasdfg', 'iloveyouiloveyou'];

function passwordProblem(string $password, string $name): ?string
{
    $lower = mb_strtolower($password);

    return match (true) {
        mb_strlen($password) < 15 => 'Use at least 15 characters',
        strlen($password) > 72 => 'Use at most 72 bytes',
        str_contains($lower, mb_strtolower($name)) => "Don't use your name",
        in_array($lower, COMMON, true) => 'This password is too common',
        default => null,
    };
}
