<?php

declare(strict_types=1);

// One member, for programs: GET /member.php?name=Amira answers {"name": "Amira", "role": "reader"}.
// A name that isn't a member gets 404, with {"error": "There is no such member."}.
// Any other method than GET gets 405, with the header Allow: GET and {"error": "Use GET."}.
// Every answer is JSON, with Content-Type: application/json.

const MEMBERS = ['Amira' => 'reader', 'Jonas' => 'staff'];

function json(int $status, mixed $data): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    json(405, ['error' => 'Use GET.']);
}
$name = is_string($_GET['name'] ?? null) ? $_GET['name'] : '';
if (!isset(MEMBERS[$name])) {
    json(404, ['error' => 'There is no such member.']);
}
json(200, ['name' => $name, 'role' => MEMBERS[$name]]);
