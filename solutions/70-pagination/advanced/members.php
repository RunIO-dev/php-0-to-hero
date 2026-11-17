<?php

declare(strict_types=1);

// The library's members, for its desk app: GET /members.php, as JSON, a page at a time.
// - ?role=reader or ?role=staff: only those members. Any other role: 400.
// - ?sort=name (the default) or ?sort=joined (the newest first); then by name. Any other sort: 400.
// - ?page= (from 1, the default 1) and ?per_page= (1 to 50, the default 10). Anything else: 400.
// - The answer: a list of {"name": ..., "role": ..., "joined": ...}, and the header
//   X-Total-Count with how many members match the filter.
// - Every 400 is problem details: type "about:blank", title "Bad Request", any detail.
// Never put text from the address into the SQL: placeholders for values, and a list of allowed sorts.

function database(): PDO
{
    $db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec('CREATE TABLE members (name TEXT NOT NULL, role TEXT NOT NULL, joined TEXT NOT NULL) STRICT');
    $add = $db->prepare('INSERT INTO members (name, role, joined) VALUES (?, ?, ?)');
    foreach ([
        ['Amira', 'reader', '2025-09-01'], ['Jonas', 'staff', '2023-02-14'], ['Kenji', 'reader', '2026-01-20'],
        ['Ana', 'reader', '2024-05-30'], ['Femi', 'staff', '2025-11-03'], ['Lena', 'reader', '2026-02-11'],
    ] as $member) {
        $add->execute($member);
    }

    return $db;
}

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

const SORTS = ['name' => 'name', 'joined' => 'joined DESC, name'];

/** A whole number from the address, $default when it is missing, or null when it isn't a number. */
function number(string $name, int $default): ?int
{
    $value = $_GET[$name] ?? null;
    if ($value === null) {
        return $default;
    }

    return is_string($value) && preg_match('/^[0-9]{1,6}$/D', $value) === 1 ? (int) $value : null;
}

$page = number('page', 1);
$perPage = number('per_page', 10);
$role = $_GET['role'] ?? '';
$sort = $_GET['sort'] ?? 'name';
if ($page === null || $page < 1 || $perPage === null || $perPage < 1 || $perPage > 50) {
    problem(400, 'Bad Request', 'page is a number from 1, per_page a number from 1 to 50.');
}
if (!in_array($role, ['', 'reader', 'staff'], true)) {
    problem(400, 'Bad Request', 'role is reader or staff.');
}
if (!is_string($sort) || !isset(SORTS[$sort])) {
    problem(400, 'Bad Request', 'sort is name or joined.');
}

$db = database();
$count = $db->prepare("SELECT COUNT(*) FROM members WHERE (:role = '' OR role = :role2)");
$count->execute(['role' => $role, 'role2' => $role]);
header('X-Total-Count: ' . (int) $count->fetchColumn());

$find = $db->prepare("SELECT name, role, joined FROM members WHERE (:role = '' OR role = :role2) ORDER BY " . SORTS[$sort] . ' LIMIT :limit OFFSET :offset');
$find->bindValue('role', $role);
$find->bindValue('role2', $role);
$find->bindValue('limit', $perPage, PDO::PARAM_INT);
$find->bindValue('offset', ($page - 1) * $perPage, PDO::PARAM_INT);
$find->execute();
json(200, $find->fetchAll());
