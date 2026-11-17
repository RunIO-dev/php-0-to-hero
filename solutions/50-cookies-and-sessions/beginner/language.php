<?php

declare(strict_types=1);

// Exercise 50, beginner: remember a language in a cookie.

header('Content-Type: text/plain; charset=UTF-8');

const LANGUAGES = ['en', 'de', 'ar'];

$lang = $_GET['lang'] ?? null;
if (in_array($lang, LANGUAGES, true)) {
    setcookie('lang', $lang, [
        'expires' => time() + 365 * 24 * 60 * 60,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    echo "Saved: $lang\n";
    exit;
}

$saved = $_COOKIE['lang'] ?? 'en';
echo 'Language: ', in_array($saved, LANGUAGES, true) ? $saved : 'en', "\n";
