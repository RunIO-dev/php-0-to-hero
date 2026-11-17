<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class NotesTest extends TestCase
{
    private const string AMIRA = 'Authorization: Bearer 8a5770ac735337001a23e4459d91f5375e5921bfc3d5761e81a03fe652ce6622';
    private const string JONAS = 'Authorization: Bearer a7718969db50b2a7e121b28e5b2dd8141699f77cfcf69878e5c791e0dc811ec0';

    public function testEachMemberReadsTheirOwnNotes(): void
    {
        $amira = self::send('GET /notes.php', self::AMIRA);
        $jonas = self::send('GET /notes.php', self::JONAS);

        self::assertSame([200, [
            ['id' => 1, 'book' => 'Kindred', 'text' => 'Read chapter 3 again.'],
            ['id' => 3, 'book' => 'Sula', 'text' => 'Recommend to the reading group.'],
        ]], [$amira['status'], json_decode($amira['body'], true)]);
        self::assertSame([['id' => 2, 'book' => 'Kitchen', 'text' => 'Order a second copy.']], json_decode($jonas['body'], true));
    }

    public function testWithoutAKnownTokenTheApiAsksForOne(): void
    {
        $tries = ['', 'Authorization: Bearer ' . str_repeat('0', 64), 'Authorization: Basic QW1pcmE6dGVh',
            'Authorization: Bearer f73a54b8cb9daa9366fd9032253cc10e6bb08caff2d7eee18c2a38c98d425601'];
        foreach ($tries as $header) {
            $response = self::send('GET /notes.php', $header);

            self::assertSame([401, 'Bearer', 'application/problem+json'], [$response['status'], $response['headers']['www-authenticate'] ?? null, $response['headers']['content-type'] ?? null], $header);
        }
        self::assertSame(401, self::send('DELETE /notes.php?id=1', '')['status']);
    }

    public function testAMemberDeletesTheirOwnNote(): void
    {
        $response = self::send('DELETE /notes.php?id=3', self::AMIRA);

        self::assertSame([204, ''], [$response['status'], $response['body']]);
    }

    public function testSomeoneElsesNoteLooksMissing(): void
    {
        $theirs = self::send('DELETE /notes.php?id=2', self::AMIRA);
        $missing = self::send('DELETE /notes.php?id=9', self::AMIRA);

        self::assertSame([404, 'application/problem+json'], [$theirs['status'], $theirs['headers']['content-type'] ?? null]);
        self::assertSame('There is no note 2.', json_decode($theirs['body'], true)['detail'] ?? null);
        self::assertSame([404, 'There is no note 9.'], [$missing['status'], json_decode($missing['body'], true)['detail'] ?? null]);
    }

    public function testOtherMethodsAre405(): void
    {
        $response = self::send('PUT /notes.php?id=1', self::AMIRA);

        self::assertSame([405, 'GET, DELETE'], [$response['status'], $response['headers']['allow'] ?? null]);
    }

    /** @return array{status: int, headers: array<string, string>, body: string} */
    private static function send(string $request, string $header): array
    {
        return responses_of(dirname(subject(__DIR__, 'notes.php')), $request . ($header === '' ? '' : "\n$header") . "\n")[0];
    }
}
