<?php

declare(strict_types=1);

// Exercise 52, advanced: a store for uploaded images.

final readonly class Storage
{
    private const array TYPES = ['image/png' => 'png', 'image/jpeg' => 'jpg'];

    public function __construct(private string $dir) {}

    public function save(string $path): string
    {
        $type = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        $extension = is_string($type) ? self::TYPES[$type] ?? null : null;
        if ($extension === null) {
            throw new InvalidArgumentException('Only PNG and JPEG images can be stored.');
        }
        $name = bin2hex(random_bytes(16)) . ".$extension";
        if (!copy($path, "$this->dir/$name")) {
            throw new RuntimeException("Cannot store $name");
        }

        return $name;
    }

    /** @return array{type: string, path: string}|null */
    public function open(string $name): ?array
    {
        if (preg_match('/^[0-9a-f]{32}\.(png|jpg)$/', $name, $m) !== 1 || !is_file("$this->dir/$name")) {
            return null;
        }

        return ['type' => array_search($m[1], self::TYPES, true) ?: 'image/png', 'path' => "$this->dir/$name"];
    }
}
