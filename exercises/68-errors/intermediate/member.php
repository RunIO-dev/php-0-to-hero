<?php

declare(strict_types=1);

// One member, for programs, at /member.php?name=Amira. Its errors are JSON, but each in a shape of
// its own. Answer them as problem details (RFC 9457), with Content-Type: application/problem+json:
// - an unknown name: 404, {"type": "about:blank", "title": "Not Found", "status": 404, "detail": "There is no member called \"Nobody\"."}
// - a method other than GET: 405, the header Allow: GET, and title "Method Not Allowed", detail "Use GET."

const MEMBERS = ['Amira' => 'reader', 'Jonas' => 'staff'];

function json(int $status, mixed $data): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json(405, ['message' => 'wrong method']);
}
$name = is_string($_GET['name'] ?? null) ? $_GET['name'] : '';
if (!isset(MEMBERS[$name])) {
    json(404, ['error' => true, 'reason' => 'unknown']);
}
json(200, ['name' => $name, 'role' => MEMBERS[$name]]);
