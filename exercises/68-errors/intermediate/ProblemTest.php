<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ProblemTest extends TestCase
{
    public function testAMemberIsStillPlainJson(): void
    {
        [$amira] = self::send('GET /member.php?name=Amira');

        self::assertSame([200, 'application/json', ['name' => 'Amira', 'role' => 'reader']], self::read($amira));
    }

    public function testAnUnknownNameIsAProblem(): void
    {
        [$nobody] = self::send('GET /member.php?name=Nobody');

        self::assertSame([404, 'application/problem+json', [
            'type' => 'about:blank', 'title' => 'Not Found', 'status' => 404, 'detail' => 'There is no member called "Nobody".',
        ]], self::read($nobody));
    }

    public function testAWrongMethodIsAProblem(): void
    {
        [$delete] = self::send('DELETE /member.php?name=Amira');

        self::assertSame([405, 'application/problem+json', [
            'type' => 'about:blank', 'title' => 'Method Not Allowed', 'status' => 405, 'detail' => 'Use GET.',
        ]], self::read($delete));
        self::assertSame('GET', $delete['headers']['allow'] ?? null, 'Say which method is allowed, in the Allow header.');
    }

    /** @return list<array{status: int, headers: array<string, string>, body: string}> */
    private static function send(string $http): array
    {
        return responses_of(dirname(subject(__DIR__, 'member.php')), "$http\n");
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
