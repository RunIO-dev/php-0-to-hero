<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LoansTest extends TestCase
{
    public function testAGoodLoanIsCreated(): void
    {
        [$loan] = self::post('{"title": "Kindred", "member": "Amira"}');

        self::assertSame([201, 'application/json', ['id' => 7, 'title' => 'Kindred', 'member' => 'Amira']], self::read($loan));
        self::assertSame('/loan.php?id=7', $loan['headers']['location'] ?? null, 'Say where the new loan is, in the Location header.');
    }

    public function testABodyThatIsNotJsonIs400(): void
    {
        foreach (['{"title": "Kindred",', '', 'title=Kindred&member=Amira'] as $body) {
            [$loan] = self::post($body);

            self::assertSame([400, 'application/json', ['error' => 'The body must be JSON.']], self::read($loan), $body);
        }
    }

    public function testAnUnknownOrMissingFieldIs422(): void
    {
        $wrong = ['{"title": "Sula", "member": "Amira"}', '{"title": "Kindred", "member": "Nobody"}', '{"title": "Kindred"}', '["Kindred", "Amira"]', '"Kindred"', '{"title": ["Kindred"], "member": "Amira"}'];
        foreach ($wrong as $body) {
            [$loan] = self::post($body);

            self::assertSame([422, 'application/json', ['error' => 'A loan needs a known title and a known member.']], self::read($loan), $body);
            self::assertArrayNotHasKey('location', $loan['headers'], $body);
        }
    }

    public function testOnlyPostIsAllowed(): void
    {
        [$get] = responses_of(dirname(subject(__DIR__, 'loans.php')), "GET /loans.php\n");

        self::assertSame([405, 'application/json', ['error' => 'Use POST.']], self::read($get));
        self::assertSame('POST', $get['headers']['allow'] ?? null);
    }

    /** @return list<array{status: int, headers: array<string, string>, body: string}> */
    private static function post(string $body): array
    {
        return responses_of(dirname(subject(__DIR__, 'loans.php')), "POST /loans.php\nContent-Type: application/json\n\n$body\n");
    }

    /**
     * @param array{status: int, headers: array<string, string>, body: string} $response
     * @return array{int, ?string, mixed}
     */
    private static function read(array $response): array
    {
        return [$response['status'], $response['headers']['content-type'] ?? null, json_decode($response['body'], true)];
    }
}
