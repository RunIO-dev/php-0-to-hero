<?php

declare(strict_types=1);

// An unsafe upload: kept under the name the visitor chose, inside public/.

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_array($_FILES['cover'] ?? null)) {
    $file = $_FILES['cover'];
    if (!is_dir(__DIR__ . '/uploads')) {
        mkdir(__DIR__ . '/uploads');
    }
    move_uploaded_file((string) $file['tmp_name'], __DIR__ . '/uploads/' . $file['name']);
    echo "Saved as /uploads/{$file['name']}\n";
}
