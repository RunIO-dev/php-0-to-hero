<?php

declare(strict_types=1);

// Logging in changes what a session may do, so it gets a new ID: session_regenerate_id(true).

header('Content-Type: text/plain; charset=UTF-8');

session_start(['cookie_httponly' => true, 'cookie_secure' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);
$before = session_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_regenerate_id(true); // a new ID; the old one is deleted
    $_SESSION['member'] = 'Amira';
}

echo 'Member: ', is_string($_SESSION['member'] ?? null) ? $_SESSION['member'] : 'nobody', "\n";
echo 'Same ID as before this request: ', session_id() === $before ? 'yes' : 'no', "\n";
