<?php

declare(strict_types=1);

// Exercise 64, beginner: security headers.

header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

$title = is_string($_GET['title'] ?? null) ? $_GET['title'] : 'Kindred';
?>
<h1><?= htmlspecialchars($title, ENT_QUOTES) ?></h1>
