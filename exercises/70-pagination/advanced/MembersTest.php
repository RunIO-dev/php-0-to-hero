<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MembersTest extends TestCase
{
    public function testTheFirstPageSortedByName(): void
    {
        $response = self::get('/members.php?per_page=4');

        self::assertSame(200, $response['status']);
        self::assertSame(['Amira', 'Ana', 'Femi', 'Jonas'], self::names($response));
        self::assertSame('6', $response['headers']['x-total-count'] ?? null, 'Send the number of matching members in X-Total-Count.');
        self::assertSame(['name' => 'Amira', 'role' => 'reader', 'joined' => '2025-09-01'], json_decode($response['body'], true)[0] ?? null);
    }

    public function testTheDefaultIsTenAndTheSecondPageHasTheRest(): void
    {
        self::assertCount(6, self::names(self::get('/members.php')));
        self::assertSame(['Kenji', 'Lena'], self::names(self::get('/members.php?per_page=4&page=2')));
        self::assertSame([], self::names(self::get('/members.php?page=9')));
    }

    public function testFilterByRoleAndSortByJoined(): void
    {
        $readers = self::get('/members.php?role=reader&sort=joined');

        self::assertSame(['Lena', 'Kenji', 'Amira', 'Ana'], self::names($readers));
        self::assertSame('4', $readers['headers']['x-total-count'] ?? null);
        self::assertSame(['Femi'], self::names(self::get('/members.php?role=staff&sort=joined&per_page=1')));
    }

    public function testWrongParametersAre400(): void
    {
        $wrong = ['page=0', 'page=two', 'per_page=51', 'per_page=0', 'role=admin', 'sort=password', 'sort=name;DROP%20TABLE%20members', 'sort[]=name', 'role[]=reader'];
        foreach ($wrong as $query) {
            $response = self::get("/members.php?$query");

            self::assertSame([400, 'application/problem+json'], [$response['status'], $response['headers']['content-type'] ?? null], $query);
            self::assertSame('Bad Request', json_decode($response['body'], true)['title'] ?? null, $query);
        }
    }

    /** @return array{status: int, headers: array<string, string>, body: string} */
    private static function get(string $path): array
    {
        return responses_of(dirname(subject(__DIR__, 'members.php')), "GET $path\n")[0];
    }

    /**
     * @param array{status: int, headers: array<string, string>, body: string} $response
     * @return list<mixed>
     */
    private static function names(array $response): array
    {
        $members = json_decode($response['body'], true);

        return is_array($members) ? array_column($members, 'name') : [];
    }
}
