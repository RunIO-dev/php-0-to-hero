<?php

declare(strict_types=1);

namespace Shelf\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shelf\Isbn;

final class IsbnTest extends TestCase
{
    #[DataProvider('valid')]
    public function testAcceptsAValidIsbn(string $input): void
    {
        self::assertSame('9780385474542', new Isbn($input)->digits);
    }

    #[DataProvider('invalid')]
    public function testRejectsAnInvalidIsbn(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Isbn($input);
    }

    /** @return array<string, array{string}> */
    public static function valid(): array
    {
        return [
            'only digits' => ['9780385474542'],
            'with hyphens' => ['978-0-385-47454-2'],
            'with spaces' => ['978 0 385 47454 2'],
        ];
    }

    /** @return array<string, array{string}> */
    public static function invalid(): array
    {
        return [
            'wrong check digit' => ['9780385474543'],
            'too short' => ['978038547454'],
            'not 978 or 979' => ['9770385474542'],
            'letters' => ['978038547454X'],
            'empty' => [''],
        ];
    }
}
