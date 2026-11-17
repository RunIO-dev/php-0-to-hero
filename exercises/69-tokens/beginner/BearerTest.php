<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'bearer.php');

final class BearerTest extends TestCase
{
    public function testTheTokenAfterBearer(): void
    {
        self::assertSame('8a5770ac', bearerToken('Bearer 8a5770ac'));
        self::assertSame('8a5770ac', bearerToken('bearer 8a5770ac'));
        self::assertSame('8a5770ac', bearerToken('  BEARER   8a5770ac  '));
    }

    public function testNoTokenIsNull(): void
    {
        self::assertNull(bearerToken(null));
        self::assertNull(bearerToken(''));
        self::assertNull(bearerToken('Bearer'));
        self::assertNull(bearerToken('Bearer   '));
    }

    public function testOtherSchemesAndBrokenTokensAreNull(): void
    {
        self::assertNull(bearerToken('Basic QW1pcmE6dGVh'));
        self::assertNull(bearerToken('Bearer 8a57 70ac'));
        self::assertNull(bearerToken('Bearer8a5770ac'));
        self::assertNull(bearerToken('Token 8a5770ac'));
    }
}
