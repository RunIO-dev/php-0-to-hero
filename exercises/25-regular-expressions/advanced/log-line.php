<?php

declare(strict_types=1);

// Exercise 25, advanced: reading a log line.
//
// The library's loan log has lines like
//   2026-11-17 LEND "Kindred" amira@example.org
//   2026-11-20 RETURN "Things Fall Apart" jonas@example.org
// Write a function parseLogLine(string $line): ?array that returns
//   ['date' => '2026-11-17', 'action' => 'LEND', 'title' => 'Kindred', 'email' => 'amira@example.org']
// or null when the line doesn't have that shape (the action is LEND or
// RETURN; the title is in double quotes). Use named groups. The email only
// needs to be one word here; check it with filter_var() afterwards: an
// invalid email also gives null.
//
// Check your work: composer exercise 25 advanced
