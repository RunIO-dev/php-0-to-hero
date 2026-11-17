<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SubscribeTest extends TestCase
{
    private const POST = "POST /subscribe.php\nContent-Type: application/x-www-form-urlencoded\n\n";

    public function testAValidAddressRedirectsWith303(): void
    {
        [$response] = responses_of(dirname(subject(__DIR__, 'subscribe.php')), self::POST . "email=ana%40example.org\n");

        self::assertSame(303, $response['status']);
        self::assertSame('/thanks.php', $response['headers']['location'] ?? null);
    }

    public function testAWrongAddressComesBackWith422AndTheTextEscaped(): void
    {
        [$response] = responses_of(dirname(subject(__DIR__, 'subscribe.php')), self::POST . "email=%22%3Eana\n");

        self::assertSame(422, $response['status']);
        self::assertStringContainsString('<p class="error">An email address, please.</p>', $response['body']);
        self::assertStringContainsString('value="&quot;&gt;ana"', $response['body']);
    }

    public function testTheFormAloneShowsNoError(): void
    {
        [$response] = responses_of(dirname(subject(__DIR__, 'subscribe.php')), "GET /subscribe.php\n");

        self::assertSame(200, $response['status']);
        self::assertStringNotContainsString('error', $response['body']);
    }
}
