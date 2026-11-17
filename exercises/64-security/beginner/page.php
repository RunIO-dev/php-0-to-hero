<?php

declare(strict_types=1);

// Exercise 64, beginner: security headers.
//
// Send these headers with the page, before any output:
// - Content-Security-Policy: default-src 'self'; frame-ancestors 'none'
// - X-Content-Type-Options: nosniff
// - Referrer-Policy: same-origin

$title = is_string($_GET['title'] ?? null) ? $_GET['title'] : 'Kindred';
?>
<h1><?= htmlspecialchars($title, ENT_QUOTES) ?></h1>
<?php // Check your work: composer exercise 64 beginner
