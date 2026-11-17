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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    problem(405, 'Method Not Allowed', 'Use POST.');
}
$type = is_string($_SERVER['CONTENT_TYPE'] ?? null) ? $_SERVER['CONTENT_TYPE'] : '';
if (!str_starts_with($type, 'application/json')) {
    problem(415, 'Unsupported Media Type', 'Send the member as application/json.');
}
try {
    $sent = json_decode((string) file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException) {
    problem(400, 'Bad Request', 'The body is not valid JSON.');
}

$name = is_array($sent) && is_string($sent['name'] ?? null) ? trim($sent['name']) : '';
$email = is_array($sent) ? $sent['email'] ?? null : null;
$role = is_array($sent) ? $sent['role'] ?? null : null;
$errors = [];
if (mb_strlen($name) < 2 || mb_strlen($name) > 40) {
    $errors[] = ['field' => 'name', 'detail' => 'A name has 2 to 40 characters.'];
}
if (!is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errors[] = ['field' => 'email', 'detail' => 'An email address is needed.'];
}
if (!in_array($role, ['reader', 'staff'], true)) {
    $errors[] = ['field' => 'role', 'detail' => 'The role is reader or staff.'];
}
if ($errors !== []) {
    $wrong = count($errors) === 1 ? 'One field is' : count($errors) . ' fields are';
    problem(422, 'The member is not valid', "$wrong wrong.", '/problems/invalid-member', ['errors' => $errors]);
}
if (in_array(mb_strtolower($name), array_map(mb_strtolower(...), MEMBERS), true)) {
    problem(409, 'The member is already there', "There is already a member called \"$name\".", '/problems/duplicate-member');
}

header('Location: /member.php?name=' . rawurlencode($name));
json(201, ['name' => $name, 'email' => $email, 'role' => $role]);
