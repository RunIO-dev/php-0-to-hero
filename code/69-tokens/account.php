<?php

declare(strict_types=1);

// What this example's pages share: the members, their tokens, and who sent a request.

/** As a database would hold them: only the SHA-256 of each token, never the token itself. */
const TOKENS = [
    'f73a54b8cb9daa9366fd9032253cc10e6bb08caff2d7eee18c2a38c98d425601' => 'Amira',
    '45da181bab1798a9b75e587b0bca27fad40e1b37ae47c49acd03778d231d86a0' => 'Jonas',
];

const ROLES = ['Amira' => 'reader', 'Jonas' => 'staff'];

const FLAGS = JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

function json(int $status, mixed $data): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, FLAGS), "\n";
    exit;
}

function problem(int $status, string $title, string $detail): never
{
    http_response_code($status);
    header('Content-Type: application/problem+json');
    echo json_encode(['type' => 'about:blank', 'title' => $title, 'status' => $status, 'detail' => $detail], FLAGS), "\n";
    exit;
}

/** The member whose token came with this request, in the header "Authorization: Bearer <token>", or null. */
function member(): ?string
{
    $header = is_string($_SERVER['HTTP_AUTHORIZATION'] ?? null) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
    if (preg_match('/^Bearer ([0-9a-f]{64})$/D', $header, $m) !== 1) {
        return null;
    }

    return TOKENS[hash('sha256', $m[1])] ?? null;
}

/** Stops the request unless a member with one of these roles sent it; returns the member's name. */
function allow(string ...$roles): string
{
    $member = member();
    if ($member === null) {
        header('WWW-Authenticate: Bearer');
        problem(401, 'Unauthorized', 'Send a valid token: Authorization: Bearer <token>.');
    }
    if (!in_array(ROLES[$member], $roles, true)) {
        problem(403, 'Forbidden', 'Only staff may do this.');
    }

    return $member;
}
