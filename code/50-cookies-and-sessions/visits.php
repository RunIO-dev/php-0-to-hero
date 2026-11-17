<?php

declare(strict_types=1);

// A session: the browser keeps only a random ID in a cookie; the data stays on the server.

header('Content-Type: text/plain; charset=UTF-8');

session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true, // refuse IDs that this server didn't create
]);

$visits = $_SESSION['visits'] ?? 0;
$_SESSION['visits'] = (is_int($visits) ? $visits : 0) + 1;

echo "Visits in this session: {$_SESSION['visits']}\n";
