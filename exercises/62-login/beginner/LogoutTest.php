<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LogoutTest extends TestCase
{
    private const string LOGIN = "# capture token /name=\"_token\" value=\"([^\"]+)\"/\nGET /login.php\n\n###\n"
        . "POST /login.php\nContent-Type: application/x-www-form-urlencoded\n\nname=Amira&password=tea+kettle+under+the+lemon+tree&_token={{token}}";

    public function testLoggingOutForgetsTheMember(): void
    {
        [, $login, $logout, $home] = self::afterLogin("POST /logout.php\nContent-Type: application/x-www-form-urlencoded\n\n_token={{token}}", 'GET /home.php');

        self::assertSame(303, $login['status']);
        self::assertSame([303, '/home.php'], [$logout['status'], self::header($logout, 'Location')]);
        self::assertNotNull(self::header($logout, 'Set-Cookie'), 'Give the session a new ID: session_regenerate_id(true)');
        self::assertStringContainsString('Not logged in', $home['body']);
    }

    public function testOnlyAPostWithTheTokenLogsOut(): void
    {
        [, , $get, $forged, $home] = self::afterLogin(
            'GET /logout.php',
            "POST /logout.php\nContent-Type: application/x-www-form-urlencoded\n\n_token=guessed",
            'GET /home.php',
        );

        self::assertSame([405, 'POST'], [$get['status'], self::header($get, 'Allow')]);
        self::assertSame(403, $forged['status']);
        self::assertStringContainsString('Logged in as Amira', $home['body']);
    }

    /** @return list<array{status: int, headers: array<string, string>, body: string}> */
    private static function afterLogin(string ...$requests): array
    {
        return responses_of(dirname(subject(__DIR__, 'logout.php')), implode("\n\n###\n", [self::LOGIN, ...$requests]) . "\n");
    }

    /** @param array{status: int, headers: array<string, string>, body: string} $response */
    private static function header(array $response, string $name): ?string
    {
        foreach ($response['headers'] as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }

        return null;
    }
}
