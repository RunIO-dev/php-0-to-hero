<?php

declare(strict_types=1);

// Each member's notes on books, for the library's app. The token comes in "Authorization: Bearer <token>".
// - GET /notes.php: the notes of the member whose token came with the request, as a JSON list of
//   {"id": ..., "book": ..., "text": ...}, without the member.
// - DELETE /notes.php?id=2: removes one of the member's own notes: 204, and no body.
//   A note that doesn't exist, or belongs to someone else, gets the same answer: 404 as problem details,
//   detail "There is no note 2." (so nobody learns which notes exist).
// - No token, or one the API doesn't know: 401, the header WWW-Authenticate: Bearer, and problem details.
// - Any other method: 405, Allow: GET, DELETE.
// Amira's token is 8a5770ac735337001a23e4459d91f5375e5921bfc3d5761e81a03fe652ce6622, Jonas's a7718969db50b2a7e121b28e5b2dd8141699f77cfcf69878e5c791e0dc811ec0.

/** As a database would hold them: only the SHA-256 of each token. */
const TOKENS = [
    'f73a54b8cb9daa9366fd9032253cc10e6bb08caff2d7eee18c2a38c98d425601' => 'Amira',
    '45da181bab1798a9b75e587b0bca27fad40e1b37ae47c49acd03778d231d86a0' => 'Jonas',
];

const NOTES = [
    1 => ['id' => 1, 'member' => 'Amira', 'book' => 'Kindred', 'text' => 'Read chapter 3 again.'],
    2 => ['id' => 2, 'member' => 'Jonas', 'book' => 'Kitchen', 'text' => 'Order a second copy.'],
    3 => ['id' => 3, 'member' => 'Amira', 'book' => 'Sula', 'text' => 'Recommend to the reading group.'],
];

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

// Everyone gets every note.
json(200, array_values(NOTES));
