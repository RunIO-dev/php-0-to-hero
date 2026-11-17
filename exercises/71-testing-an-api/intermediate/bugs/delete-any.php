<?php

declare(strict_types=1);

// Bug: any member can delete any note.

// Each member's notes, as the library's API answers them, as a function.

const NOTES = [
    1 => ['member' => 'Amira', 'text' => 'Read chapter 3 again.'],
    2 => ['member' => 'Jonas', 'text' => 'Order a second copy.'],
];

/**
 * GET /notes: the caller's own notes, as a JSON list of {"id", "text"}.
 * DELETE /notes?id=N: removes one of the caller's own notes: 204, no body. Someone else's note, or one
 * that doesn't exist: 404 problem details, the same for both.
 * Without a known token, in "Authorization: Bearer <token>": 401, with the header WWW-Authenticate: Bearer.
 *
 * @return array{status: int, headers: array<string, string>, body: string}
 */
function notesAnswer(string $method, string $authorization, int $id = 0): array
{
    $member = preg_match('/^Bearer (\S+)$/D', $authorization, $m) === 1 ? memberOf($m[1]) : null;
    if ($member === null) {
        return problem(401, 'Unauthorized', 'Send a valid token.', ['WWW-Authenticate' => 'Bearer']);
    }
    if ($method === 'DELETE') {
        if (!isset(NOTES[$id])) {
            return problem(404, 'Not Found', "There is no note $id.");
        }

        return ['status' => 204, 'headers' => [], 'body' => ''];
    }
    $own = [];
    foreach (NOTES as $noteId => $note) {
        if ($note['member'] === $member) {
            $own[] = ['id' => $noteId, 'text' => $note['text']];
        }
    }

    return ['status' => 200, 'headers' => ['Content-Type' => 'application/json'], 'body' => json_encode($own, JSON_THROW_ON_ERROR)];
}

/** The member a token belongs to, found by its hash. Amira's token is "amira-token", Jonas's "jonas-token". */
function memberOf(string $token): ?string
{
    $tokens = [hash('sha256', 'amira-token') => 'Amira', hash('sha256', 'jonas-token') => 'Jonas'];

    return $tokens[hash('sha256', $token)] ?? null;
}

/**
 * @param array<string, string> $headers
 * @return array{status: int, headers: array<string, string>, body: string}
 */
function problem(int $status, string $title, string $detail, array $headers = []): array
{
    $body = json_encode(['type' => 'about:blank', 'title' => $title, 'status' => $status, 'detail' => $detail], JSON_THROW_ON_ERROR);

    return ['status' => $status, 'headers' => ['Content-Type' => 'application/problem+json', ...$headers], 'body' => $body];
}
