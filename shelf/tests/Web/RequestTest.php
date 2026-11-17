<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use JsonException;
use PHPUnit\Framework\TestCase;
use Shelf\Web\Request;

final class RequestTest extends TestCase
{
    public function testTheTokenComesFromTheAuthorizationHeader(): void
    {
        self::assertSame('abc123', self::with(['authorization' => 'Bearer abc123'])->bearerToken());
        self::assertSame('abc123', self::with(['authorization' => ' bearer  abc123 '])->bearerToken(), 'The scheme is case-insensitive');
        self::assertNull(self::with(['authorization' => 'Basic QW1pcmE6dGVh'])->bearerToken());
        self::assertNull(self::with(['authorization' => 'Bearer '])->bearerToken());
        self::assertNull(self::with(['authorization' => 'Bearer one two'])->bearerToken());
        self::assertNull(self::with([])->bearerToken());
    }

    public function testJsonIsRecognisedByItsContentType(): void
    {
        self::assertTrue(self::with(['content-type' => 'application/json'])->isJson());
        self::assertTrue(self::with(['content-type' => 'application/json; charset=utf-8'])->isJson());
        self::assertFalse(self::with(['content-type' => 'application/jsonx'])->isJson());
        self::assertFalse(self::with(['content-type' => 'application/x-www-form-urlencoded'])->isJson());
    }

    public function testABrokenBodyThrows(): void
    {
        self::assertSame(['title' => 'Sula'], new Request('POST', '/api/books', body: '{"title": "Sula"}')->json());

        $this->expectException(JsonException::class);

        new Request('POST', '/api/books', body: '{"title":')->json();
    }

    /** @param array<string, string> $headers */
    private static function with(array $headers): Request
    {
        return new Request('GET', '/api/loans', headers: $headers);
    }
}
