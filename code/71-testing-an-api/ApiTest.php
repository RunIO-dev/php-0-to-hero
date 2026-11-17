<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/api.php';
require_once __DIR__ . '/shape.php';

final class ApiTest extends TestCase
{
    /** What programs rely on: the names and types of a book's fields, and of a problem's. */
    private const array BOOK = ['id' => 'int', 'title' => 'string', 'author' => 'string', 'year' => 'int'];

    private const array PROBLEM = ['type' => 'string', 'title' => 'string', 'status' => 'int', 'detail' => 'string'];

    public function testTheListIsJsonOfBooks(): void
    {
        $answer = handle('GET', '/books');
        $books = json_decode($answer['body'], true, flags: JSON_THROW_ON_ERROR);

        self::assertSame([200, 'application/json'], [$answer['status'], $answer['headers']['Content-Type'] ?? null]);
        self::assertIsArray($books);
        self::assertTrue(array_is_list($books), 'A list, [...], not an object');
        foreach ($books as $book) {
            self::assertSame([], shapeErrors(self::BOOK, $book));
        }
    }

    public function testOneBookHasTheSameShape(): void
    {
        $answer = handle('GET', '/book', ['id' => '2']);

        self::assertSame(200, $answer['status']);
        self::assertSame([], shapeErrors(self::BOOK, json_decode($answer['body'], true)));
        self::assertSame('Kindred', json_decode($answer['body'], true)['title'] ?? null);
    }

    public function testANewBookIsWhereLocationSays(): void
    {
        $answer = handle('POST', '/books', body: '{"title": "Sula", "author": "Toni Morrison", "year": 1973}');

        self::assertSame([201, '/book?id=4'], [$answer['status'], $answer['headers']['Location'] ?? null]);
        self::assertSame([], shapeErrors(self::BOOK, json_decode($answer['body'], true)));
    }

    /** @param array<string, string> $query */
    #[DataProvider('mistakes')]
    public function testEveryMistakeIsAProblem(string $method, string $path, array $query, string $body, int $status): void
    {
        $answer = handle($method, $path, $query, $body);
        $problem = json_decode($answer['body'], true);

        self::assertSame([$status, 'application/problem+json'], [$answer['status'], $answer['headers']['Content-Type'] ?? null]);
        self::assertSame([], shapeErrors(self::PROBLEM, $problem));
        self::assertSame($status, is_array($problem) ? $problem['status'] : null, 'The body says the same status');
    }

    /** @return iterable<string, array{string, string, array<string, string>, string, int}> */
    public static function mistakes(): iterable
    {
        yield 'an unknown book' => ['GET', '/book', ['id' => '9'], '', 404];
        yield 'an unknown address' => ['GET', '/authors', [], '', 404];
        yield 'a method the address does not take' => ['DELETE', '/books', [], '', 405];
        yield 'a body that is not JSON' => ['POST', '/books', [], '{"title": ', 400];
        yield 'a year as text' => ['POST', '/books', [], '{"title": "Sula", "author": "Toni Morrison", "year": "1973"}', 422];
        yield 'a missing author' => ['POST', '/books', [], '{"title": "Sula", "year": 1973}', 422];
    }

    public function testA405SaysWhatIsAllowed(): void
    {
        self::assertSame('GET, POST', handle('PUT', '/books')['headers']['Allow'] ?? null);
    }
}
