<?php

declare(strict_types=1);

final class Book
{
    public function __construct(public readonly string $title) {}
}

final class Ebook extends Book {}
