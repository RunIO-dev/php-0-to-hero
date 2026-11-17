<?php

declare(strict_types=1);

// A new member, sent by the library's desk app: POST /members.php with {"name": "Kenji", "email": "kenji@example.com", "role": "reader"}.
// Answer every mistake as problem details (problem() below), in this order:
// 1. A method other than POST: 405, Allow: POST, title "Method Not Allowed", detail "Use POST."
// 2. A Content-Type that doesn't start with application/json: 415, "Unsupported Media Type", "Send the member as application/json."
// 3. A body that isn't valid JSON: 400, "Bad Request", "The body is not valid JSON."
// 4. Wrong fields: 422, type "/problems/invalid-member", title "The member is not valid",
//    detail "One field is wrong." or "2 fields are wrong." (3 ...), and "errors": [{"field": ..., "detail": ...}],
//    one for each wrong field, in the order name, email, role:
//    - name: text of 2 to 40 characters, after trim: "A name has 2 to 40 characters."
//    - email: a valid address (filter_var with FILTER_VALIDATE_EMAIL): "An email address is needed."
//    - role: "reader" or "staff": "The role is reader or staff."
// 5. A name that is already a member, in any case: 409, type "/problems/duplicate-member",
//    title "The member is already there", detail "There is already a member called \"amira\"." (the name as sent)
// 6. Otherwise: 201, Location: /member.php?name=Kenji, and {"name": ..., "email": ..., "role": ...} (the name trimmed).

const MEMBERS = ['Amira', 'Jonas'];

const FLAGS = JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

function json(int $status, mixed $data): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, FLAGS), "\n";
    exit;
}

/** @param array<string, mixed> $more */
function problem(int $status, string $title, string $detail, string $type = 'about:blank', array $more = []): never
{
    http_response_code($status);
    header('Content-Type: application/problem+json');
    echo json_encode(['type' => $type, 'title' => $title, 'status' => $status, 'detail' => $detail, ...$more], FLAGS), "\n";
    exit;
}

$sent = json_decode((string) file_get_contents('php://input'), true);
json(201, $sent);
