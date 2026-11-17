<?php

declare(strict_types=1);

// Exercise 48, beginner: a front controller for a small site.

header('Content-Type: text/plain; charset=UTF-8');

const PAGES = [
    '/' => 'Home',
    '/about' => 'About us',
    '/opening' => 'Monday to Friday, 9 to 18',
];

$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!isset(PAGES[$path])) {
    http_response_code(404);
    echo "Not found\n";
} else {
    echo PAGES[$path], "\n";
}
