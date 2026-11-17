<?php

declare(strict_types=1);

// ✗ JSON put together from strings: a quote in the title ends the string, and the rest becomes JSON.

header('Content-Type: application/json');

$title = is_string($_GET['title'] ?? null) ? $_GET['title'] : '';
echo '{"title": "' . $title . '", "copies": 1}';
