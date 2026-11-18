<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'smoke.php');

final class SmokeTest extends TestCase
{
    /** @return array<string, array{status: int, headers: array<string, string>}> */
    private static function good(): array
    {
        return [
            '/' => ['status' => 200, 'headers' => [
                'content-type' => 'text/html; charset=UTF-8',
                'content-security-policy' => "default-src 'self'",
                'strict-transport-security' => 'max-age=31536000',
            ]],
            '/api/books' => ['status' => 200, 'headers' => ['content-type' => 'application/json']],
            '/add' => ['status' => 302, 'headers' => ['location' => 'https://shelf.example/login']],
            '/robots.txt' => ['status' => 404, 'headers' => []],
        ];
    }

    public function testAGoodDeploy(): void
    {
        self::assertSame([], smokeProblems(self::good()));
    }

    public function testAWrongStatus(): void
    {
        $answers = self::good();
        $answers['/']['status'] = 500;

        self::assertSame(['GET /: 500 instead of 200'], smokeProblems($answers));
    }

    public function testMissingAndWrongHeaders(): void
    {
        $answers = self::good();
        unset($answers['/']['headers']['content-security-policy'], $answers['/']['headers']['strict-transport-security']);
        $answers['/api/books']['headers']['content-type'] = 'text/html; charset=UTF-8';
        $answers['/add']['headers']['location'] = 'http://shelf.example/login';

        self::assertSame([
            'GET /: no content-security-policy',
            'GET /: no strict-transport-security',
            'GET /api/books: content-type is text/html; charset=UTF-8, not application/json',
            'GET /add: location is http://shelf.example/login, not https://',
        ], smokeProblems($answers));
    }

    public function testAPathWithNoAnswer(): void
    {
        $answers = self::good();
        unset($answers['/api/books']);

        self::assertSame(['GET /api/books: no answer'], smokeProblems($answers));
    }

    public function testEverythingWrongInOrder(): void
    {
        $answers = ['/' => ['status' => 502, 'headers' => []], '/add' => ['status' => 200, 'headers' => []]];

        self::assertSame([
            'GET /: 502 instead of 200',
            'GET /: no content-security-policy',
            'GET /: no strict-transport-security',
            'GET /api/books: no answer',
            'GET /add: 200 instead of 302',
            'GET /add: no location',
        ], smokeProblems($answers));
    }
}
