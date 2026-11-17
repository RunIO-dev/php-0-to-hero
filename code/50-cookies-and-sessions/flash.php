<?php

declare(strict_types=1);

// A flash message: saved in the session before a redirect, shown once on the next page, then gone.

header('Content-Type: text/plain; charset=UTF-8');

session_start(['cookie_httponly' => true, 'cookie_secure' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['flash'] = 'Saved.';
    header('Location: /flash.php', true, 303);
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
echo is_string($flash) ? "Message: $flash\n" : "No message\n";
