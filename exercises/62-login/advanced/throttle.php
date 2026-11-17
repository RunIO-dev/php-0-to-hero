<?php

declare(strict_types=1);

// Exercise 62, advanced: slow down password guessing.
//
// The table: login_failures (name TEXT NOT NULL, at TEXT NOT NULL), one row per failed login, with
// the time as 'Y-m-d H:i:s'. Names are compared without case.
//
// - recordFailure($db, $name, $now): note a failed login for $name at $now.
// - isBlocked($db, $name, $now): true when $name has 5 or more failed logins in the 15 minutes
//   before $now. The login page checks this first, and refuses without checking the password.
// - forgetFailures($db, $name): after a successful login, the count starts again.
// Use prepared statements.

const MAX_FAILURES = 5;
const WINDOW_MINUTES = 15;

function recordFailure(PDO $db, string $name, DateTimeImmutable $now): void {}

function isBlocked(PDO $db, string $name, DateTimeImmutable $now): bool
{
    return false;
}

function forgetFailures(PDO $db, string $name): void {}
// Check your work: composer exercise 62 advanced
