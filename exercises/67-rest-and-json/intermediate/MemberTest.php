<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MemberTest extends TestCase
{
    public function testAMemberIsJson(): void
    {
        [$amira, $jonas] = self::send("GET /member.php?name=Amira\n\n###\nGET /member.php?name=Jonas");

        self::assertSame([200, 'application/json', ['name' => 'Amira', 'role' => 'reader']], self::read($amira));
        self::assertSame([200, 'application/json', ['name' => 'Jonas', 'role' => 'staff']], self::read($jonas));
    }

    public function testAnUnknownNameIs404(): void
    {
        [$nobody, $none] = self::send("GET /member.php?name=Nobody\n\n###\nGET /member.php");

        self::assertSame([404, 'application/json', ['error' => 'There is no such member.']], self::read($nobody));
        self::assertSame([404, 'application/json', ['error' => 'There is no such member.']], self::read($none));
    }

    public function testOnlyGetIsAllowed(): void
    {
        [$post] = self::send("POST /member.php?name=Amira\nContent-Type: application/json\n\n{}");

        self::assertSame([405, 'application/json', ['error' => 'Use GET.']], self::read($post));
        self::assertSame('GET', $post['headers']['allow'] ?? null, 'Say which method is allowed, in the Allow header.');
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
