<?php

declare(strict_types=1);

// A cookie: the browser keeps a small value for this site, and sends it back with every request.

header('Content-Type: text/plain; charset=UTF-8');

if (isset($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'], true)) {
    setcookie('theme', $_GET['theme'], [
        'expires' => time() + 365 * 24 * 60 * 60, // a year; without it, the cookie ends with the browser
        'path' => '/',
        'secure' => true,     // only over HTTPS (and on localhost, while you develop)
        'httponly' => true,   // not readable by JavaScript in the page
        'samesite' => 'Lax',  // not sent along when another site posts a form to this one
    ]);
    echo "Theme saved: {$_GET['theme']}\n";
    exit;
}

$theme = $_COOKIE['theme'] ?? 'light';
echo 'Theme: ', in_array($theme, ['light', 'dark'], true) ? $theme : 'light', "\n";
