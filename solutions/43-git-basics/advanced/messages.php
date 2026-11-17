<?php

declare(strict_types=1);

// Exercise 43, advanced: check a commit message. A solution.

/** @return list<string> */
function messageProblems(string $message): array
{
    if (trim($message) === '') {
        return ['empty message'];
    }
    $lines = explode("\n", $message);
    $summary = $lines[0];
    $firstWord = explode(' ', trim($summary))[0];
    $problems = [];
    if (mb_strlen($summary) > 72) {
        $problems[] = 'summary longer than 72 characters';
    }
    if (preg_match('/^\p{Lu}/u', $summary) !== 1) {
        $problems[] = 'start the summary with a capital letter';
    }
    if (str_ends_with($summary, '.')) {
        $problems[] = 'no full stop at the end of the summary';
    }
    if (mb_strlen($firstWord) > 4 && preg_match('/(ed|ing)$/i', $firstWord) === 1) {
        $problems[] = 'write the summary as an instruction: Add, not Added';
    }
    if (isset($lines[1]) && trim($lines[1]) !== '') {
        $problems[] = 'leave a blank line after the summary';
    }

    return $problems;
}
