<?php

declare(strict_types=1);

// Exercise 61, intermediate: rules for a new password.
//
// passwordProblem($password, $name) returns what is wrong with a member's new password, or null when
// it is fine. Check in this order, and return the first message that applies, exactly as written:
//
// - fewer than 15 characters (letters, not bytes):  "Use at least 15 characters"
// - more than 72 bytes (bcrypt reads no more):       "Use at most 72 bytes"
// - it contains the member's name, in any case:      "Don't use your name"
// - it is one of COMMON, in any case:                "This password is too common"

const COMMON = ['password1234567', '123456789012345', 'qwertyuiopasdfg', 'iloveyouiloveyou'];

function passwordProblem(string $password, string $name): ?string
{
    return null;
}
// Check your work: composer exercise 61 intermediate
