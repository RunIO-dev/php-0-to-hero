<?php

declare(strict_types=1);

// Bug: an unknown title is answered with status 200.

// One book of the library's API, as a function: what GET /api/book?title=... answers.

const CATALOGUE = [
    'kindred' => ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979],
    'kitchen' => ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988],
];

/**
 * A book found in any case: 200, Content-Type application/json, and {"title", "author", "year"} with the
 * year as a number. An unknown title: 404, Content-Type application/problem+json, and problem details
 * {"type": "about:blank", "title": "Not Found", "status": 404, "detail": "There is no book called \"...\"."}.
 *
 * @return array{status: int, headers: array<string, string>, body: string}
 */
function bookAnswer(string $title): array
{
    $book = CATALOGUE[mb_strtolower(trim($title))] ?? null;
    if ($book === null) {
        $problem = ['type' => 'about:blank', 'title' => 'Not Found', 'status' => 404, 'detail' => "There is no book called \"$title\"."];

        return ['status' => 200, 'headers' => ['Content-Type' => 'application/problem+json'], 'body' => json_encode($problem, JSON_THROW_ON_ERROR)];
    }

    return ['status' => 200, 'headers' => ['Content-Type' => 'application/json'], 'body' => json_encode($book, JSON_THROW_ON_ERROR)];
}
