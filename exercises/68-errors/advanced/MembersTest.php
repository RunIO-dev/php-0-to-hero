<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MembersTest extends TestCase
{
    public function testAGoodMemberIsCreated(): void
    {
        $response = self::post('{"name": " Kenji ", "email": "kenji@example.com", "role": "reader"}');

        self::assertSame([201, 'application/json', ['name' => 'Kenji', 'email' => 'kenji@example.com', 'role' => 'reader']], self::read($response));
        self::assertSame('/member.php?name=Kenji', $response['headers']['location'] ?? null);
    }

    public function testOnlyPostIsAllowed(): void
    {
        [$get] = responses_of(self::dir(), "GET /members.php\n");

        self::assertSame([405, 'application/problem+json'], array_slice(self::read($get), 0, 2));
        self::assertSame('POST', $get['headers']['allow'] ?? null);
        self::assertSame(['about:blank', 'Method Not Allowed', 405, 'Use POST.'], self::problem($get));
    }

    public function testAFormIs415AndBrokenJsonIs400(): void
    {
        [$form] = responses_of(self::dir(), "POST /members.php\nContent-Type: application/x-www-form-urlencoded\n\nname=Kenji\n");
        $broken = self::post('{"name": "Kenji",');

        self::assertSame(['about:blank', 'Unsupported Media Type', 415, 'Send the member as application/json.'], self::problem($form));
        self::assertSame(415, $form['status']);
        self::assertSame(['about:blank', 'Bad Request', 400, 'The body is not valid JSON.'], self::problem($broken));
        self::assertSame(400, $broken['status']);
    }

    public function testEveryWrongFieldIsNamedAtOnce(): void
    {
        $all = self::post('{"name": " K ", "email": "kenji at example", "role": "admin"}');
        $one = self::post('{"name": "Kenji", "email": "kenji@example.com", "role": "Reader"}');

        self::assertSame([422, 'application/problem+json', [
            'type' => '/problems/invalid-member', 'title' => 'The member is not valid', 'status' => 422, 'detail' => '3 fields are wrong.',
            'errors' => [
                ['field' => 'name', 'detail' => 'A name has 2 to 40 characters.'],
                ['field' => 'email', 'detail' => 'An email address is needed.'],
                ['field' => 'role', 'detail' => 'The role is reader or staff.'],
            ],
        ]], self::read($all));
        self::assertSame('One field is wrong.', self::read($one)[2]['detail'] ?? null);
        self::assertSame([['field' => 'role', 'detail' => 'The role is reader or staff.']], self::read($one)[2]['errors'] ?? null);
    }

    public function testFieldsOfTheWrongTypeAreWrong(): void
    {
        $response = self::post('{"name": ["Kenji"], "email": 7, "role": null}');

        self::assertSame(422, $response['status']);
        self::assertSame(['name', 'email', 'role'], array_column(self::read($response)[2]['errors'] ?? [], 'field'));
        self::assertSame(422, self::post('["Kenji"]')['status']);
    }

    public function testAKnownNameIs409InAnyCase(): void
    {
        $response = self::post('{"name": "amira", "email": "amira@example.com", "role": "reader"}');

        self::assertSame(['/problems/duplicate-member', 'The member is already there', 409, 'There is already a member called "amira".'], self::problem($response));
        self::assertSame(409, $response['status']);
    }

    private static function dir(): string
    {
        return dirname(subject(__DIR__, 'members.php'));
    }

    /** @return array{status: int, headers: array<string, string>, body: string} */
    private static function post(string $body): array
    {
        return responses_of(self::dir(), "POST /members.php\nContent-Type: application/json\n\n$body\n")[0];
    }

    /**
     * @param array{status: int, headers: array<string, string>, body: string} $response
     * @return array{int, ?string, mixed}
     */
    private static function read(array $response): array
    {
        return [$response['status'], $response['headers']['content-type'] ?? null, json_decode($response['body'], true)];
    }

    /**
     * @param array{status: int, headers: array<string, string>, body: string} $response
     * @return list<mixed> type, title, status and detail
     */
    private static function problem(array $response): array
    {
        $data = json_decode($response['body'], true);

        return is_array($data) ? [$data['type'] ?? null, $data['title'] ?? null, $data['status'] ?? null, $data['detail'] ?? null] : [];
    }
}
