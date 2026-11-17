<?php

declare(strict_types=1);

// Exercise 52, intermediate: what a file really is.

const IMAGES = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];

function imageExtension(string $path): ?string
{
    $type = (new finfo(FILEINFO_MIME_TYPE))->file($path);

    return is_string($type) ? IMAGES[$type] ?? null : null;
}
