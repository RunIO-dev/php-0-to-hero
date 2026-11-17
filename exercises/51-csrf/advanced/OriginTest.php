<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'origin.php');

final class OriginTest extends TestCase
{
    private const OURS = 'https://shelf.example';

    /** @return iterable<string, array{array<string, string>, bool}> */
    public static function requests(): iterable
    {
        yield 'same origin' => [['sec-fetch-site' => 'same-origin'], true];
        yield 'typed address' => [['sec-fetch-site' => 'none'], true];
        yield 'another site' => [['sec-fetch-site' => 'cross-site'], false];
        yield 'a sibling site' => [['sec-fetch-site' => 'same-site', 'origin' => self::OURS], false];
        yield 'our origin' => [['origin' => self::OURS], true];
        yield 'another origin' => [['origin' => 'https://stranger.example'], false];
        yield 'a look-alike' => [['origin' => 'https://shelf.example.stranger.example'], false];
        yield 'another port' => [['origin' => 'https://shelf.example:8443'], false];
        yield 'plain http' => [['origin' => 'http://shelf.example'], false];
        yield 'origin null' => [['origin' => 'null'], false];
        yield 'our referer' => [['referer' => 'https://shelf.example/add?x=1'], true];
        yield 'our referer, default port' => [['referer' => 'https://shelf.example:443/add'], true];
        yield 'look-alike referer' => [['referer' => 'https://shelf.example.stranger.example/'], false];
        yield 'referer without a host' => [['referer' => '/add'], false];
        yield 'nothing' => [[], false];
    }

    /** @param array<string, string> $headers */
    #[DataProvider('requests')]
    public function testOnlyOurOwnPagesCount(array $headers, bool $ours): void
    {
        self::assertSame($ours, fromOurSite($headers, self::OURS));
    }
}
