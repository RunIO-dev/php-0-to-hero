<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShelfCodeTest extends TestCase
{
    public function testAValidCode(): void
    {
        require_once subject(__DIR__, 'shelf-code.php');

        $code = new ShelfCode('  sf-012 ');

        self::assertSame('SF-012', $code->code);
        self::assertSame('SF', $code->section());
        self::assertSame(12, $code->number());
        self::assertSame(300, (new ShelfCode('HI-300'))->number());
    }

    public function testEqualCodes(): void
    {
        require_once subject(__DIR__, 'shelf-code.php');

        self::assertTrue((new ShelfCode('SF-012'))->equals(new ShelfCode('sf-012')));
        self::assertFalse((new ShelfCode('SF-012'))->equals(new ShelfCode('SF-013')));
    }

    /** @return array<string, array{string}> */
    public static function invalid(): array
    {
        return [
            'two digits' => ['SF-12'],
            'four digits' => ['SF-0123'],
            'no hyphen' => ['SF012'],
            'three letters' => ['SFX-012'],
            'text around it' => ['shelf SF-012'],
            'empty' => [''],
        ];
    }

    #[DataProvider('invalid')]
    public function testInvalidCodes(string $input): void
    {
        require_once subject(__DIR__, 'shelf-code.php');

        $this->expectException(InvalidArgumentException::class);
        new ShelfCode($input);
    }

    public function testItCantChange(): void
    {
        require_once subject(__DIR__, 'shelf-code.php');

        self::assertTrue((new ReflectionClass(ShelfCode::class))->isReadOnly(), 'Make the class readonly.');
    }
}
