<?php

declare(strict_types=1);

// Sends a stored cover. The name must have the shape upload.php gives, so no other file can be asked for.

$file = $_GET['file'] ?? '';
if (!is_string($file) || preg_match('/^[0-9a-f]{32}\.(png|jpg)$/', $file, $m) !== 1 || !is_file(__DIR__ . "/../storage/$file")) {
    http_response_code(404);
    exit;
}
header('Content-Type: ' . ($m[1] === 'png' ? 'image/png' : 'image/jpeg'));
header('X-Content-Type-Options: nosniff'); // the browser must believe the Content-Type, not guess
header('Content-Length: ' . filesize(__DIR__ . "/../storage/$file"));
readfile(__DIR__ . "/../storage/$file");
