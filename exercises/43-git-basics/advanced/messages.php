<?php

declare(strict_types=1);

// Exercise 43, advanced: check a commit message.
//
// messageProblems() returns what is wrong with a commit message, in this order:
// - "empty message" when there is nothing but spaces (and nothing else then);
// - "summary longer than 72 characters" (the summary is the first line);
// - "start the summary with a capital letter";
// - "no full stop at the end of the summary";
// - "write the summary as an instruction: Add, not Added" when its first word,
//   longer than 4 letters, ends in "ed" or "ing" (Added, Fixing; but "Need" is fine);
// - "leave a blank line after the summary" when there is a second line and it isn't empty.

/** @return list<string> */
function messageProblems(string $message): array
{
    return []; // your code here
}

// Check your work: composer exercise 43 advanced
