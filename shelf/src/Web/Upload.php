<?php

declare(strict_types=1);

namespace Shelf\Web;

/** A file sent with a form: PHP's error code, and where PHP keeps the file until the request ends. */
final readonly class Upload
{
    public function __construct(
        public int $error,
        public string $path = '',
    ) {}

    public function isEmpty(): bool
    {
        return $this->error === UPLOAD_ERR_NO_FILE;
    }
}
