<?php

declare(strict_types=1);

// A new loan, sent by the library's desk app: POST /loans.php with a JSON body such as
// {"title": "Kindred", "member": "Amira"}.
// - Body that isn't JSON: 400, {"error": "The body must be JSON."}
// - Not an object with a "title" and a "member" that are known (in BOOKS and MEMBERS): 422,
//   {"error": "A loan needs a known title and a known member."}
// - Otherwise: 201, the header Location: /loan.php?id=7 and the loan {"id": 7, "title": ..., "member": ...}.
//   (This exercise saves nothing; every new loan gets the next ID, 7.)
// - Any other method: 405, Allow: POST, {"error": "Use POST."}
// Every answer is JSON, with Content-Type: application/json.

const BOOKS = ['Kindred', 'Kitchen', 'Things Fall Apart'];
const MEMBERS = ['Amira', 'Jonas'];
const NEXT_ID = 7;

function json(int $status, mixed $data): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    json(405, ['error' => 'Use POST.']);
}
try {
    $sent = json_decode((string) file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException) {
    json(400, ['error' => 'The body must be JSON.']);
}
$title = is_array($sent) ? $sent['title'] ?? null : null;
$member = is_array($sent) ? $sent['member'] ?? null : null;
if (!in_array($title, BOOKS, true) || !in_array($member, MEMBERS, true)) {
    json(422, ['error' => 'A loan needs a known title and a known member.']);
}

header('Location: /loan.php?id=' . NEXT_ID);
json(201, ['id' => NEXT_ID, 'title' => $title, 'member' => $member]);
