<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PageTest extends TestCase
{
    public function testThePageSendsTheSecurityHeaders(): void
    {
        [$page] = responses_of(dirname(subject(__DIR__, 'page.php')), "GET /page.php?title=Sula\n");
        $headers = array_change_key_case($page['headers']);

        self::assertSame("default-src 'self'; frame-ancestors 'none'", $headers['content-security-policy'] ?? null);
        self::assertSame('nosniff', $headers['x-content-type-options'] ?? null);
        self::assertSame('same-origin', $headers['referrer-policy'] ?? null);
        self::assertStringContainsString('<h1>Sula</h1>', $page['body']);
    }
}
