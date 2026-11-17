<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tools\Links;

final class LinksTest extends TestCase
{
    /** @return iterable<string, array{string, bool}> */
    public static function urls(): iterable
    {
        yield 'own computer' => ['http://localhost:8000/hello.php', true];
        yield 'loopback' => ['http://127.0.0.1:8000', true];
        yield 'example domain' => ['https://shelf.example/delete', true];
        yield 'example.org' => ['https://example.org', true];
        yield 'no host' => ['https://', true];
        yield 'real page' => ['https://www.php.net/manual/en/', false];
        yield 'look-alike' => ['https://example.org.evil.net', false];
    }

    #[DataProvider('urls')]
    public function testExampleAddressesAreNotChecked(string $url, bool $example): void
    {
        self::assertSame($example, Links::isExample($url));
    }
}
