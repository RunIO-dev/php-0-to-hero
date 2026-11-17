<?php

declare(strict_types=1);

// The small API of chapters 67 and 68 as one function: a request in, an answer out, no server.
// Tests call it directly, as Shelf's tests call App::handle().

const BOOKS = [
    1 => ['id' => 1, 'title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958],
    2 => ['id' => 2, 'title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979],
    3 => ['id' => 3, 'title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988],
];

/**
 * @param array<string, string> $query
 * @return array{status: int, headers: array<string, string>, body: string}
 */
function handle(string $method, string $path, array $query = [], string $body = ''): array
{
    $allowed = ['/books' => 'GET, POST', '/book' => 'GET'][$path] ?? null;
    if ($allowed === null) {
        return problem(404, 'Not Found', "There is no address $path.");
    }
    if (!in_array($method, explode(', ', $allowed), true)) {
        return problem(405, 'Method Not Allowed', "Use $allowed.", ['Allow' => $allowed]);
    }
    if ($path === '/book') {
        $book = BOOKS[(int) ($query['id'] ?? 0)] ?? null;

        return $book === null ? problem(404, 'Not Found', 'There is no such book.') : answer(200, $book);
    }
    if ($method === 'GET') {
        return answer(200, array_values(BOOKS));
    }

    try {
        $sent = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return problem(400, 'Bad Request', 'The body is not valid JSON.');
    }
    if (!is_array($sent) || !is_string($sent['title'] ?? null) || !is_string($sent['author'] ?? null) || !is_int($sent['year'] ?? null)) {
        return problem(422, 'Unprocessable Content', 'A book needs a title, an author and a year, as a number.');
    }

    return answer(201, ['id' => 4, 'title' => $sent['title'], 'author' => $sent['author'], 'year' => $sent['year']], ['Location' => '/book?id=4']);
}

/**
 * @param array<string, string> $headers
 * @return array{status: int, headers: array<string, string>, body: string}
 */
function answer(int $status, mixed $data, array $headers = []): array
{
    return ['status' => $status, 'headers' => ['Content-Type' => 'application/json', ...$headers], 'body' => json_encode($data, JSON_THROW_ON_ERROR) . "\n"];
}

/**
 * @param array<string, string> $headers
 * @return array{status: int, headers: array<string, string>, body: string}
 */
function problem(int $status, string $title, string $detail, array $headers = []): array
{
    $body = json_encode(['type' => 'about:blank', 'title' => $title, 'status' => $status, 'detail' => $detail], JSON_THROW_ON_ERROR);

    return ['status' => $status, 'headers' => ['Content-Type' => 'application/problem+json', ...$headers], 'body' => "$body\n"];
}
