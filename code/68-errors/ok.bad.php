<?php

declare(strict_types=1);

// ✗ Every answer is 200 OK, and the error is somewhere in the body, in a shape of its own.

header('Content-Type: application/json');

$id = is_string($_GET['id'] ?? null) ? (int) $_GET['id'] : 0;
if ($id !== 2) {
    echo json_encode(['ok' => false, 'message' => 'not found'], JSON_THROW_ON_ERROR);
    exit;
}
echo json_encode(['ok' => true, 'book' => ['id' => 2, 'title' => 'Kindred']], JSON_THROW_ON_ERROR);
