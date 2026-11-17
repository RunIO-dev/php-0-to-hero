<?php

declare(strict_types=1);

// One member, for programs, at /member.php?name=Amira. Its errors are JSON, but each in a shape of
// its own. Answer them as problem details (RFC 9457), with Content-Type: application/problem+json:
// - an unknown name: 404, {"type": "about:blank", "title": "Not Found", "status": 404, "detail": "There is no member called \"Nobody\"."}
// - a method other than GET: 405, the header Allow: GET, and title "Method Not Allowed", detail "Use GET."

const MEMBERS = ['Amira' => 'reader', 'Jonas' => 'staff'];

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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    problem(405, 'Method Not Allowed', 'Use GET.');
}
$name = is_string($_GET['name'] ?? null) ? $_GET['name'] : '';
if (!isset(MEMBERS[$name])) {
    problem(404, 'Not Found', "There is no member called \"$name\".");
}
json(200, ['name' => $name, 'role' => MEMBERS[$name]]);
