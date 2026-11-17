<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LanguageTest extends TestCase
{
    public function testTheLanguageIsSavedAndSentBack(): void
    {
        [$before, $saved, $after] = responses_of(
            dirname(subject(__DIR__, 'language.php')),
            "GET /language.php\n\n###\nGET /language.php?lang=de\n\n###\nGET /language.php\n",
        );

        self::assertSame("Language: en\n", $before['body']);
        self::assertSame("Saved: de\n", $saved['body']);
        self::assertSame("Language: de\n", $after['body']);
    }

    public function testTheCookieHasSafeSettings(): void
    {
        [$saved] = responses_of(dirname(subject(__DIR__, 'language.php')), "GET /language.php?lang=ar\n");
        $cookie = $saved['headers']['set-cookie'] ?? '';

        self::assertStringStartsWith('lang=ar;', $cookie);
        foreach (['Max-Age=31536000', 'path=/', 'secure', 'HttpOnly', 'SameSite=Lax'] as $setting) {
            self::assertStringContainsString($setting, $cookie);
        }
    }

    public function testAForgedCookieCountsAsEnglish(): void
    {
        [$forged] = responses_of(dirname(subject(__DIR__, 'language.php')), "GET /language.php\nCookie: lang=%3Cscript%3E\n");

        self::assertSame("Language: en\n", $forged['body']);
    }
}
