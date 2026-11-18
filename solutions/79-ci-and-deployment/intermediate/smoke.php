<?php

declare(strict_types=1);

// The smoke test after a deploy (chapter 79): a few requests to the live site, enough to know that it
// answers as it should. smokeProblems($answers) gets the answers, by path:
//   ["/" => ["status" => 200, "headers" => ["content-type" => "text/html; charset=UTF-8", ...]], ...]
// (header names in lower case), and checks each path of CHECKS, in that order:
// - a path with no answer:  "GET /api/books: no answer"   (and nothing else for that path)
// - a wrong status:         "GET /: 500 instead of 200"
// - a missing header:       "GET /: no content-security-policy"
// - a header that doesn't start with its value: "GET /api/books: content-type is text/html, not application/json"
// For each path: the status first, then its headers in the order of CHECKS. Answers to other paths
// are ignored.

const CHECKS = [
    '/' => ['status' => 200, 'headers' => ['content-security-policy' => '', 'strict-transport-security' => 'max-age=']],
    '/api/books' => ['status' => 200, 'headers' => ['content-type' => 'application/json']],
    '/add' => ['status' => 302, 'headers' => ['location' => 'https://']],
];

/**
 * @param array<string, array{status: int, headers: array<string, string>}> $answers
 * @return list<string>
 */
function smokeProblems(array $answers): array
{
    $problems = [];
    foreach (CHECKS as $path => $check) {
        $answer = $answers[$path] ?? null;
        if ($answer === null) {
            $problems[] = "GET $path: no answer";
            continue;
        }
        if ($answer['status'] !== $check['status']) {
            $problems[] = "GET $path: {$answer['status']} instead of {$check['status']}";
        }
        foreach ($check['headers'] as $name => $start) {
            $value = $answer['headers'][$name] ?? null;
            if ($value === null) {
                $problems[] = "GET $path: no $name";
            } elseif (!str_starts_with($value, $start)) {
                $problems[] = "GET $path: $name is $value, not $start";
            }
        }
    }

    return $problems;
}
