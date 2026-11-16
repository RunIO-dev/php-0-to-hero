<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShelfCodeTest extends TestCase
{
    /** @return array<string, array{string, bool}> */
    public static function codes(): array
    {
        return [
            'science fiction' => ['SF-012', true],
            'history' => ['HI-300', true],
            'small letters' => ['sf-012', false],
            'two digits' => ['SF-12', false],
            'four digits' => ['SF-0123', false],
            'no hyphen' => ['SF012', false],
            'text around it' => ['shelf SF-012', false],
            'a line break' => ["SF-012\n", false],
            'empty' => ['', false],
        ];
    }

    #[DataProvider('codes')]
    public function testShelfCode(string $code, bool $expected): void
    {
        require_once subject(__DIR__, 'shelf-code.php');

        self::assertSame($expected, isShelfCode($code));
    }
}
