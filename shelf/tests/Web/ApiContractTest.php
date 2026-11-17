<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Shelf\ApiTokens;
use Shelf\Library;
use Shelf\Tests\JsonShape;

/**
 * The API's contract (chapter 71), back in chapter 77 for the Symfony version: what programs rely on.
 * If a field disappears or changes its type, an error stops being problem details, or an address
 * forgets its token check, a test here fails.
 */
final class ApiContractTest extends ShelfWebTestCase
{
    private const array BOOK = ['title' => 'string', 'author' => 'string', 'year' => 'int', 'copies' => 'int', 'isbn' => '?string', 'cover' => '?string'];

    private const array LOAN = ['title' => 'string', 'due' => 'string'];

    private const array TOKEN = ['token' => 'string', 'expires_in' => 'int'];

    private const array PROBLEM = ['type' => 'string', 'title' => 'string', 'status' => 'int', 'detail' => 'string'];

    /** @var array<string, string> each member's token, issued at their first request */
    private array $tokens = [];

    public function testABookHasTheSameShapeEverywhere(): void
    {
        $list = $this->send('GET', '/api/books');
        $one = $this->send('GET', '/api/book?title=Kindred');
        $added = $this->send('POST', '/api/books', '{"title": "Sula", "author": "Toni Morrison", "year": 1973}', 'Jonas');

        self::assertCount(1, $list);
        foreach ([...$list, $one, $added] as $book) {
            self::assertSame([], JsonShape::errors(self::BOOK, $book));
        }
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('Location', '/api/book?title=Sula');
    }

    public function testAListHasItsLinks(): void
    {
        $this->send('POST', '/api/books', '{"title": "Sula", "author": "Toni Morrison", "year": 1973}', 'Jonas');

        $page = $this->send('GET', '/api/books?per_page=1');

        self::assertSame(['Kindred'], array_column($page, 'title'));
        self::assertStringContainsString('</api/books?page=2&per_page=1>; rel="next"', (string) $this->client->getResponse()->headers->get('Link'));
    }

    public function testALoanHasItsShapeAndADate(): void
    {
        self::service(Library::class)->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $loans = $this->send('GET', '/api/loans', member: 'Amira');

        self::assertCount(1, $loans);
        self::assertSame([], JsonShape::errors(self::LOAN, $loans[0]));
        self::assertIsArray($loans[0]);
        self::assertSame('2026-03-16', $loans[0]['due'], 'Two weeks, as a date');
    }

    public function testATokenHasItsShapeAndWorks(): void
    {
        $answer = $this->send('POST', '/api/tokens', json_encode(['name' => 'Amira', 'password' => self::AMIRA], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);
        self::assertSame([], JsonShape::errors(self::TOKEN, $answer));
        self::assertIsString($answer['token']);
        $this->client->request('GET', '/api/loans', server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $answer['token']]);
        self::assertResponseIsSuccessful();
    }

    public function testATokenThatWasRevokedIsRefused(): void
    {
        $this->send('DELETE', '/api/tokens', member: 'Amira');
        self::assertResponseStatusCodeSame(204);

        $this->send('GET', '/api/loans', member: 'Amira');
        self::assertResponseStatusCodeSame(401);
    }

    /** Every way a request can go wrong: the status, as problem details. */
    #[DataProvider('mistakes')]
    public function testEveryMistakeIsAProblem(string $method, string $path, string $body, ?string $token, int $status): void
    {
        $problem = $this->send($method, $path, $body, $token);

        self::assertResponseStatusCodeSame($status);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertSame([], JsonShape::errors(self::PROBLEM + ($status === 422 ? ['errors' => 'array'] : []), $problem));
        self::assertSame($status, $problem['status'] ?? null, 'The body says the same status');
    }

    /** @return iterable<string, array{string, string, string, ?string, int}> */
    public static function mistakes(): iterable
    {
        $book = '{"title": "Sula", "author": "Toni Morrison", "year": 1973}';
        yield 'an unknown address' => ['GET', '/api/authors', '', null, 404];
        yield 'a method the address does not take' => ['PUT', '/api/books', '', null, 405];
        yield 'an unknown book' => ['GET', '/api/book?title=Sula', '', null, 404];
        yield 'a page that does not exist' => ['GET', '/api/books?page=0', '', null, 400];
        yield 'a page too long' => ['GET', '/api/books?per_page=101', '', null, 400];
        yield 'a wrong password' => ['POST', '/api/tokens', '{"name": "Amira", "password": "tea kettle"}', null, 401];
        yield 'no token' => ['GET', '/api/loans', '', null, 401];
        yield 'an unknown token' => ['GET', '/api/loans', '', 'Nobody', 401];
        yield 'a reader adding a book' => ['POST', '/api/books', $book, 'Amira', 403];
        yield 'a body that is not JSON' => ['POST', '/api/books', '{"title": ', 'Jonas', 400];
        yield 'a year as text' => ['POST', '/api/books', '{"title": "Sula", "author": "Toni Morrison", "year": "1973"}', 'Jonas', 422];
        yield 'a book that is there' => ['POST', '/api/books', '{"title": "kindred", "author": "Octavia E. Butler", "year": 1979}', 'Jonas', 409];
    }

    /** Every address that isn't public asks for a token: a new one that forgets its check fails here. */
    #[DataProvider('protectedAddresses')]
    public function testAnAddressThatIsNotPublicNeedsAToken(string $method, string $path): void
    {
        $this->send($method, $path, '{}');

        self::assertResponseStatusCodeSame(401);
        self::assertResponseHeaderSame('WWW-Authenticate', 'Bearer');
    }

    /** @return iterable<string, array{string, string}> */
    public static function protectedAddresses(): iterable
    {
        foreach ([['GET', '/api/loans'], ['POST', '/api/books'], ['DELETE', '/api/tokens']] as [$method, $path]) {
            yield "$method $path" => [$method, $path];
        }
    }

    public function testTheApiStartsNoSession(): void
    {
        $this->send('GET', '/api/books');
        $this->send('GET', '/api/loans', member: 'Amira');
        $this->send('POST', '/api/books', '{"title": "Sula", "author": "Toni Morrison", "year": 1973}', 'Jonas');

        self::assertSame([], $this->client->getCookieJar()->all(), 'No cookie: programs use tokens');
    }

    /**
     * Sends a request as a program does, with the token of $member when there is one, and decodes the answer.
     *
     * @return array<mixed>
     */
    private function send(string $method, string $path, string $body = '', ?string $member = null): array
    {
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        if ($member !== null) {
            $this->tokens[$member] ??= $member === 'Nobody' ? 'not-a-token' : self::service(ApiTokens::class)->issue($member, new DateTimeImmutable())['token'];
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $this->tokens[$member];
        }
        $this->client->request($method, $path, server: $server, content: $body);
        $content = (string) $this->client->getResponse()->getContent();
        if ($content === '') {
            return [];
        }
        $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }
}
