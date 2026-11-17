<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase
{
    public function testTheRightPasswordLogsInWithANewSessionId(): void
    {
        [, $login, $home] = self::tries(['Amira', 'tea kettle under the lemon tree'], 'GET /home.php');

        self::assertSame(303, $login['status']);
        self::assertSame('/home.php', self::header($login, 'Location'));
        self::assertNotNull(self::header($login, 'Set-Cookie'), 'Give the session a new ID: session_regenerate_id(true)');
        self::assertStringContainsString('Logged in as Amira', $home['body']);
    }

    public function testEveryWrongLoginGetsTheSameAnswer(): void
    {
        [, $password, $name, $spaces, $home] = self::tries(
            ['Amira', 'tea kettle'],
            ['Nobody', 'tea kettle under the lemon tree'],
            ['Amira', ' tea kettle under the lemon tree '],
            'GET /home.php',
        );

        foreach ([$password, $name, $spaces] as $answer) {
            self::assertSame(422, $answer['status']);
            self::assertStringContainsString('Wrong name or password.', $answer['body']);
        }
        self::assertStringContainsString('Not logged in', $home['body']);
    }

    public function testTheNameComesBackEscaped(): void
    {
        [, $answer] = self::tries(['"><b>Amira', 'x']);

        self::assertStringContainsString('value="&quot;&gt;&lt;b&gt;Amira"', $answer['body']);
    }

    /**
     * The login page, then one POST per [name, password] pair, then the other requests.
     *
     * @param array{string, string}|string ...$requests
     * @return list<array{status: int, headers: array<string, string>, body: string}>
     */
    private static function tries(array|string ...$requests): array
    {
        $http = ["# capture token /name=\"_token\" value=\"([^\"]+)\"/\nGET /login.php"];
        foreach ($requests as $request) {
            $http[] = is_string($request) ? $request
                : "POST /login.php\nContent-Type: application/x-www-form-urlencoded\n\n" . http_build_query(['name' => $request[0], 'password' => $request[1]]) . '&_token={{token}}';
        }

        return responses_of(dirname(subject(__DIR__, 'login.php')), implode("\n\n###\n", $http) . "\n");
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
