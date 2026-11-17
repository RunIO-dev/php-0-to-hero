<?php

declare(strict_types=1);

// Exercise 39, advanced: a small style checker.
//
// Write styleProblems(): it gets the code of a file and returns one message per
// problem, line by line, in this order and with these words:
//   "line 3: tab used to indent"         a tab anywhere in the spaces at the start of a line
//   "line 5: spaces at the end"          spaces or tabs at the end of a line
//   "line 8: longer than 120 characters" count characters, not bytes: mb_strlen
// and, last, "no new line at the end" when the code doesn't end with "\n".
// A line can have more than one problem: report them in the order above.

/** @return list<string> */
function styleProblems(string $code): array
{
    return []; // your code here
}

// Check your work: composer exercise 39 advanced
