<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AllowTest extends TestCase
{
    private const string REMOVE = "POST /remove.php\nContent-Type: application/x-www-form-urlencoded\n\ntitle=Kindred&_token={{token}}";

    public function testAVisitorIsSentToTheLogin(): void
    {
        [, $remove, $loans] = self::as(null, self::REMOVE, 'GET /loans.php');

        self::assertSame([303, 303], [$remove['status'], $loans['status']]);
        self::assertStringNotContainsString('Removed', $remove['body']);
        self::assertStringNotContainsString('Loans of', $loans['body']);
    }

    public function testAReaderMayNotRemoveABook(): void
    {
        [, , $remove, $loans] = self::as(['Amira', 'tea kettle under the lemon tree'], self::REMOVE, 'GET /loans.php');

        self::assertSame([403, "Not allowed.\n"], [$remove['status'], $remove['body']]);
        self::assertSame([200, "Loans of Amira:\n- Kindred, due 2026-03-16\n"], [$loans['status'], $loans['body']]);
    }

    public function testStaffMayRemoveABook(): void
    {
        [, , $remove] = self::as(['Jonas', 'river stones in the morning light'], self::REMOVE);

        self::assertSame([200, "Removed Kindred\n"], [$remove['status'], $remove['body']]);
    }

    /**
     * The login page, a login as [name, password] (or none), then the requests.
     *
     * @param array{string, string}|null $member
     * @return list<array{status: int, headers: array<string, string>, body: string}>
     */
    private static function as(?array $member, string ...$requests): array
    {
        $http = ["# capture token /name=\"_token\" value=\"([^\"]+)\"/\nGET /login.php"];
        if ($member !== null) {
            $http[] = "POST /login.php\nContent-Type: application/x-www-form-urlencoded\n\n" . http_build_query(['name' => $member[0], 'password' => $member[1]]) . '&_token={{token}}';
        }

        return responses_of(dirname(subject(__DIR__, 'account.php')), implode("\n\n###\n", [...$http, ...$requests]) . "\n");
    }
}
