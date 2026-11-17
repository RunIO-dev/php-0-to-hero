<?php

declare(strict_types=1);

// What PHP tells a script about the request. Plain text, so nothing in it can become HTML.

header('Content-Type: text/plain; charset=UTF-8');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$name = $_GET['name'] ?? $_POST['name'] ?? 'nobody';

echo "Method: {$_SERVER['REQUEST_METHOD']}\n";
echo "Path: $path\n";
echo "Name: $name\n";
