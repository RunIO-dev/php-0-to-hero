<?php

declare(strict_types=1);

// A form that changes data, protected only by the session: any page on the web can send it.

header('Content-Type: text/plain; charset=UTF-8');

session_start(['cookie_httponly' => true, 'cookie_secure' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);
$_SESSION['member'] ??= 'Amira'; // as if Amira had logged in

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Deleted all loans of {$_SESSION['member']}.\n";
    exit;
}
echo "Logged in as {$_SESSION['member']}.\n";
