<?php

declare(strict_types=1);

namespace Library;

final readonly class Book
{
    public function __construct(public string $title) {}
}
