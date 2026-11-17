<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'statuses.php');

final class StatusesTest extends TestCase
{
    public function testSuccess(): void
    {
        self::assertSame([200, 201, 204], [statusFor('read'), statusFor('created'), statusFor('removed, nothing to send')]);
    }

    public function testTheRequestWasWrong(): void
    {
        self::assertSame(400, statusFor('body is not valid JSON'));
        self::assertSame(415, statusFor('body is not JSON at all, but a form'));
        self::assertSame(422, statusFor('JSON with wrong data'));
        self::assertSame(404, statusFor('no such resource'));
        self::assertSame(405, statusFor('method not allowed'));
        self::assertSame(409, statusFor('already exists'));
        self::assertSame(429, statusFor('too many requests'));
    }

    public function testWhoIsAsking(): void
    {
        self::assertSame(401, statusFor('not logged in'), '401: we don\'t know who you are.');
        self::assertSame(403, statusFor('logged in, but not allowed'), '403: we know, and the answer is no.');
    }

    public function testAnUnknownSituationThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        statusFor('everything is fine, probably');
    }
}
