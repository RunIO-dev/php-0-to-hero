<?php

declare(strict_types=1);

final class Book
{
    public string $title;

    public function __construct(public string $title) {}
}
