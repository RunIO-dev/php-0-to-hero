<?php

declare(strict_types=1);

// Exercise 52, intermediate: what a file really is.
//
// imageExtension($path) looks at the file's content with finfo (FILEINFO_MIME_TYPE), never at
// its name, and returns the extension to store it with:
//   image/png → 'png', image/jpeg → 'jpg', image/webp → 'webp'
// Anything else returns null: GIF, PDF, text, PHP code, and SVG too, which is an image the
// browser can run scripts in.

function imageExtension(string $path): ?string
{
    return pathinfo($path, PATHINFO_EXTENSION);
}
// Check your work: composer exercise 52 intermediate
