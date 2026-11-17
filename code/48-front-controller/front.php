<?php

declare(strict_types=1);

// A front controller: every request comes to this one file, which decides what to answer.
// Start it with: php -S localhost:8000 front.php

header('Content-Type: text/plain; charset=UTF-8');

$method = $_SERVER['REQUEST_METHOD'];
$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/** @var array<string, array<string, Closure(): string>> path => method => what to answer */
$routes = [
    '/' => ['GET' => fn(): string => "Welcome to the library.\n"],
    '/books' => ['GET' => fn(): string => "Kindred\nKitchen\n"],
    '/hello' => ['GET' => fn(): string => 'Hello, ' . ($_GET['name'] ?? 'nobody') . "!\n"],
];

if (!isset($routes[$path])) {
    http_response_code(404);
    echo "Nothing at $path\n";
} elseif (!isset($routes[$path][$method])) {
    http_response_code(405);
    header('Allow: ' . implode(', ', array_keys($routes[$path])));
    echo "$method is not allowed here\n";
} else {
    echo $routes[$path][$method]();
}
