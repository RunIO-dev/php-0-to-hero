<?php

declare(strict_types=1);

// Exercise 17, advanced: a value that may be missing.
//
// $loans maps a title to its due date. Write two functions:
//   dueDateOf($loans, $title)  returns the due date, or null when the title is not on loan
//   loanLine($loans, $title)   returns 'Kindred: due back on 2026-12-01'
//                              or       'Sula: on the shelf'
// Give both functions full types; dueDateOf() returns ?string.
// loanLine() should use dueDateOf().
//
// Check your work: composer exercise 17 advanced
