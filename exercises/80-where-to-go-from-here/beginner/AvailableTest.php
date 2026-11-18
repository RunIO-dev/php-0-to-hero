<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'available.php');

final class AvailableTest extends TestCase
{
    private const array SINCE = [
        'str_contains' => '8.0.0',
        'array_find' => '8.4.0',
        'json_validate' => '8.3.0',
        'array_any' => '8.4.0',
    ];

    public function testTheFunctionsAServerLacks(): void
    {
        self::assertSame(['array_any', 'array_find'], missingOn('8.3.12', self::SINCE));
        self::assertSame(['array_any', 'array_find', 'json_validate'], missingOn('8.2.30', self::SINCE));
        self::assertSame([], missingOn('8.5.12', self::SINCE));
        self::assertSame([], missingOn('8.4.0', self::SINCE));
    }

    public function testAVersionWithTwoNumbersIsItsFirstRelease(): void
    {
        self::assertSame(['array_any', 'array_find'], missingOn('8.3', self::SINCE));
        self::assertSame([], missingOn('8.4', self::SINCE));
    }

    public function testAReleaseCandidateCountsAsItsRelease(): void
    {
        self::assertSame([], missingOn('8.6.0RC7', ['clamp' => '8.6.0']));
        self::assertSame([], missingOn('8.6.0-beta1', ['clamp' => '8.6.0']));
        self::assertSame(['clamp'], missingOn('8.5.12', ['clamp' => '8.6.0']));
    }

    public function testNoFunctionsLackNothing(): void
    {
        self::assertSame([], missingOn('8.5', []));
    }

    /** @return iterable<string, array{string, array<string, string>}> */
    public static function badVersions(): iterable
    {
        yield 'a word' => ['eight', self::SINCE];
        yield 'one number' => ['8', self::SINCE];
        yield 'a letter for a number' => ['8.3.x', self::SINCE];
        yield 'nothing' => ['', self::SINCE];
        yield 'a line after it' => ["8.3.12\n", self::SINCE];
        yield "a function's version" => ['8.3.12', ['array_find' => 'PHP 8.4']];
    }

    /** @param array<string, string> $since */
    #[DataProvider('badVersions')]
    public function testABadVersionIsRefused(string $version, array $since): void
    {
        $this->expectException(InvalidArgumentException::class);
        missingOn($version, $since);
    }
}
