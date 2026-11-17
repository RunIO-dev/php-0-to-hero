<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FormatTest extends TestCase
{
    public function testTheCases(): void
    {
        require_once subject(__DIR__, 'format.php');

        self::assertSame(['hardcover', 'paperback', 'ebook'], array_map(fn(Format $f): string => $f->value, Format::cases()));
        self::assertSame(Format::Ebook, Format::from('ebook'));
        self::assertNull(Format::tryFrom('audiobook'));
    }

    public function testPhysical(): void
    {
        require_once subject(__DIR__, 'format.php');

        self::assertTrue(Format::Hardcover->isPhysical());
        self::assertTrue(Format::Paperback->isPhysical());
        self::assertFalse(Format::Ebook->isPhysical());
    }
}
