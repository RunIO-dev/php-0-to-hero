<?php

declare(strict_types=1);

// Exercise 39, advanced: a small style checker. A solution.

/** @return list<string> */
function styleProblems(string $code): array
{
    $problems = [];
    $lines = explode("\n", $code);
    if (end($lines) === '') {
        array_pop($lines); // the end of the last line, not a line of its own
    }
    foreach ($lines as $i => $line) {
        $number = $i + 1;
        if (preg_match('/^[ \t]*\t/', $line) === 1) {
            $problems[] = "line $number: tab used to indent";
        }
        if (preg_match('/[ \t]$/', $line) === 1) {
            $problems[] = "line $number: spaces at the end";
        }
        if (mb_strlen($line) > 120) {
            $problems[] = "line $number: longer than 120 characters";
        }
    }
    if ($code !== '' && !str_ends_with($code, "\n")) {
        $problems[] = 'no new line at the end';
    }

    return $problems;
}
