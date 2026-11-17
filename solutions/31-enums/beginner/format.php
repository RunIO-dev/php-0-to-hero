<?php

declare(strict_types=1);

enum Format: string
{
    case Hardcover = 'hardcover';
    case Paperback = 'paperback';
    case Ebook = 'ebook';

    public function isPhysical(): bool
    {
        return match ($this) {
            Format::Hardcover, Format::Paperback => true,
            Format::Ebook => false,
        };
    }
}
