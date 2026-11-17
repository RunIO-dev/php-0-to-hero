<?php

declare(strict_types=1);

// Headers that make the browser stricter with this page: a second wall, behind escaping.

header("Content-Security-Policy: default-src 'self'; script-src 'self'; frame-ancestors 'none'; form-action 'self'; base-uri 'none'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

$name = is_string($_GET['name'] ?? null) ? $_GET['name'] : 'reader';
?>
<p>Hello, <?= htmlspecialchars($name, ENT_QUOTES) ?></p>
