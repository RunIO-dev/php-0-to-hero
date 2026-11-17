<?php

declare(strict_types=1);

// Exercise 52, advanced: a store for uploaded images.
//
// save($path): checks that the file is a PNG or JPEG by its content (finfo), and copies it into
//   the folder under a new random name: 32 hexadecimal characters (bin2hex(random_bytes(16))) and
//   .png or .jpg. Returns that name. Anything else: InvalidArgumentException. The name the
//   visitor's file had is never used; save() doesn't even get it.
// open($name): the file's type and full path, ['type' => 'image/png', 'path' => ...], for a name
//   that save() could have made and that exists. Every other name, such as '../secret.png' or
//   'x.php', returns null, so no request can reach another file.

final readonly class Storage
{
    public function __construct(private string $dir) {}

    public function save(string $path): string
    {
        return basename($path);
    }

    /** @return array{type: string, path: string}|null */
    public function open(string $name): ?array
    {
        return ['type' => 'image/png', 'path' => "$this->dir/$name"];
    }
}
// Check your work: composer exercise 52 advanced
