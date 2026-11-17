<?php

declare(strict_types=1);

// A wrong name in the data stops at once, with the name in the message.

/** @param array<string, mixed> $data */
function render(string $file, array $data): string
{
    $template = require $file;
    ob_start();
    $template(...$data);

    return (string) ob_get_clean();
}

echo render(__DIR__ . '/templates/book.php', ['titel' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979]);
