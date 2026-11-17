<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ListTest extends TestCase
{
    private const POST = "POST /list.php\nContent-Type: application/x-www-form-urlencoded\n\ntitle=";

    public function testTitlesAreRememberedOnceEach(): void
    {
        $responses = responses_of(dirname(subject(__DIR__, 'list.php')), implode("\n\n###\n", [
            self::POST . 'Kindred',
            self::POST . '+Kindred+',
            self::POST . 'Sula',
            self::POST . '',
            'GET /list.php',
        ]) . "\n");

        self::assertSame(303, $responses[0]['status']);
        self::assertSame('/list.php', $responses[0]['headers']['location'] ?? null);
        self::assertSame("2 on your list\nKindred\nSula\n", $responses[4]['body']);
    }

    public function testAFourthTitleIs422(): void
    {
        $responses = responses_of(dirname(subject(__DIR__, 'list.php')), implode("\n\n###\n", [
            self::POST . 'A', self::POST . 'B', self::POST . 'C', self::POST . 'D', 'GET /list.php',
        ]) . "\n");

        self::assertSame(422, $responses[3]['status']);
        self::assertSame("Your list is full.\n", $responses[3]['body']);
        self::assertSame("3 on your list\nA\nB\nC\n", $responses[4]['body']);
    }

    public function testTheSessionCookieIsSafe(): void
    {
        [$first] = responses_of(dirname(subject(__DIR__, 'list.php')), self::POST . "Kindred\n");
        $cookie = $first['headers']['set-cookie'] ?? '';

        foreach (['secure', 'HttpOnly', 'SameSite=Lax'] as $setting) {
            self::assertStringContainsString($setting, $cookie);
        }
    }

    public function testEveryVisitorHasTheirOwnList(): void
    {
        [, $other] = responses_of(dirname(subject(__DIR__, 'list.php')), self::POST . "Kindred\n\n###\nGET /list.php\nCookie: PHPSESSID=someone-elses\n");

        self::assertStringStartsWith('0 on your list', $other['body']);
    }
}
