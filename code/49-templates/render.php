<?php

declare(strict_types=1);

// Rendering a template: load its function, call it with the data as named arguments, and catch
// what it prints as a string.

/** @param array<string, mixed> $data */
function render(string $file, array $data): string
{
    $template = require $file;
    ob_start();
    $template(...$data);

    return (string) ob_get_clean();
}

echo render(__DIR__ . '/templates/book.php', ['title' => 'Tom & Jerry', 'author' => 'Someone', 'year' => 1940]);
