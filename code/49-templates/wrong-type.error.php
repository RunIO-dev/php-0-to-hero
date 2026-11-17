<?php

declare(strict_types=1);

// A value of the wrong type stops too: the year must be an int.

/** @param array<string, mixed> $data */
function render(string $file, array $data): string
{
    $template = require $file;
    ob_start();
    $template(...$data);

    return (string) ob_get_clean();
}

echo render(__DIR__ . '/templates/book.php', ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 'long ago']);
