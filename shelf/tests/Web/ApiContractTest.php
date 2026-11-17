<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shelf\Accounts;
use Shelf\ApiTokens;
use Shelf\Book;
use Shelf\Isbn;
use Shelf\Library;
use Shelf\Member;
use Shelf\Role;
use Shelf\Tests\ArrayLogger;
use Shelf\Tests\InMemoryBookRepository;
use Shelf\Tests\InMemoryLoanRepository;
use Shelf\Tests\InMemoryMemberRepository;
use Shelf\Tests\InMemoryTokenRepository;
use Shelf\Tests\JsonShape;
use Shelf\Web\App;
use Shelf\Web\Covers;
use Shelf\Web\Request;
use Shelf\Web\Response;
use Shelf\Web\Templates;

/**
 * The API's contract (chapter 71): what programs rely on. If a field disappears or changes its type,
 * an error stops being problem details, or an address forgets its token check, a test here fails.
 */
final class ApiContractTest extends TestCase
{
    private const array BOOK = ['title' => 'string', 'author' => 'string', 'year' => 'int', 'copies' => 'int', 'isbn' => '?string', 'cover' => '?string'];

    private const array LOAN = ['title' => 'string', 'due' => 'string'];

    private const array TOKEN = ['token' => 'string', 'expires_in' => 'int'];

    private const array PROBLEM = ['type' => 'string', 'title' => 'string', 'status' => 'int', 'detail' => 'string'];

    private App $app;
    private Library $library;
    private ArraySession $session;

    /** @var array<string, string> a token for each member, by name in lower case */
    private array $tokens = [];

    protected function setUp(): void
    {
        $books = new InMemoryBookRepository([new Book('Kindred', 'Octavia E. Butler', 1979, 2, new Isbn('9780807083697'))]);
        $members = new InMemoryMemberRepository([new Member('Amira', Role::Reader), new Member('Jonas', Role::Staff)]);
        $this->library = new Library($books, $members, new InMemoryLoanRepository($books));
        $accounts = new Accounts($members, ['cost' => 4]);
        $accounts->setPassword('Amira', 'tea kettle under the lemon tree');
        $tokens = new ApiTokens(new InMemoryTokenRepository($members), $members);
        foreach (['amira' => 'Amira', 'jonas' => 'Jonas'] as $key => $name) {
            $this->tokens[$key] = $tokens->issue($name, new DateTimeImmutable())['token'];
        }
        $this->session = new ArraySession(['csrf' => 'the-token']);
        $covers = new Covers(sys_get_temp_dir() . '/shelf-contract-' . bin2hex(random_bytes(4)));
        $this->app = new App($this->library, $accounts, new Templates(__DIR__ . '/../../templates'), $this->session, $covers, $tokens, new ArrayLogger());
    }

    public function testABookHasTheSameShapeEverywhere(): void
    {
        $list = $this->send('GET', '/api/books');
        $one = $this->send('GET', '/api/book', query: ['title' => 'Kindred']);
        $added = $this->send('POST', '/api/books', '{"title": "Sula", "author": "Toni Morrison", "year": 1973}', 'jonas');

        self::assertSame([200, 200, 201], [$list->status, $one->status, $added->status]);
        foreach ([...self::decoded($list), self::decoded($one), self::decoded($added)] as $book) {
            self::assertSame([], JsonShape::errors(self::BOOK, $book));
        }
    }

    public function testALoanHasItsShapeAndADate(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $loans = self::decoded($this->send('GET', '/api/loans', token: 'amira'));

        self::assertCount(1, $loans);
        self::assertSame([], JsonShape::errors(self::LOAN, $loans[0]));
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', is_array($loans[0]) && is_string($loans[0]['due'] ?? null) ? $loans[0]['due'] : '');
    }

    public function testATokenHasItsShape(): void
    {
        $answer = $this->send('POST', '/api/tokens', '{"name": "Amira", "password": "tea kettle under the lemon tree"}');

        self::assertSame(201, $answer->status);
        self::assertSame([], JsonShape::errors(self::TOKEN, self::decoded($answer)));
    }

    /**
     * Every way a request can go wrong: the status, as problem details.
     *
     * @param array<string, string> $query
     */
    #[DataProvider('mistakes')]
    public function testEveryMistakeIsAProblem(string $method, string $path, array $query, string $body, ?string $token, int $status): void
    {
        $answer = $this->send($method, $path, $body, $token, $query);
        $problem = self::decoded($answer);

        self::assertSame([$status, 'application/problem+json'], [$answer->status, $answer->headers['Content-Type'] ?? null]);
        self::assertSame([], JsonShape::errors(self::PROBLEM, $problem));
        self::assertSame($status, $problem['status'] ?? null, 'The body says the same status');
    }

    /** @return iterable<string, array{string, string, array<string, string>, string, ?string, int}> */
    public static function mistakes(): iterable
    {
        $book = '{"title": "Sula", "author": "Toni Morrison", "year": 1973}';
        yield 'an unknown address' => ['GET', '/api/authors', [], '', null, 404];
        yield 'a method the address does not take' => ['PUT', '/api/books', [], '', null, 405];
        yield 'an unknown book' => ['GET', '/api/book', ['title' => 'Sula'], '', null, 404];
        yield 'a page that does not exist' => ['GET', '/api/books', ['page' => '0'], '', null, 400];
        yield 'a page too long' => ['GET', '/api/books', ['per_page' => '101'], '', null, 400];
        yield 'a wrong password' => ['POST', '/api/tokens', [], '{"name": "Amira", "password": "tea kettle"}', null, 401];
        yield 'no token' => ['GET', '/api/loans', [], '', null, 401];
        yield 'a reader adding a book' => ['POST', '/api/books', [], $book, 'amira', 403];
        yield 'a body that is not JSON' => ['POST', '/api/books', [], '{"title": ', 'jonas', 400];
        yield 'a year as text' => ['POST', '/api/books', [], '{"title": "Sula", "author": "Toni Morrison", "year": "1973"}', 'jonas', 422];
        yield 'a book that is there' => ['POST', '/api/books', [], '{"title": "kindred", "author": "Octavia E. Butler", "year": 1979}', 'jonas', 409];
    }

    /** Every address that isn't public asks for a token: a new one that forgets its check fails here. */
    #[DataProvider('protectedAddresses')]
    public function testAnAddressThatIsNotPublicNeedsAToken(string $method, string $path): void
    {
        $answer = $this->send($method, $path, '{}');

        self::assertSame([401, 'Bearer'], [$answer->status, $answer->headers['WWW-Authenticate'] ?? null]);
    }

    /** @return iterable<string, array{string, string}> */
    public static function protectedAddresses(): iterable
    {
        foreach ([['GET', '/api/loans'], ['POST', '/api/books'], ['DELETE', '/api/tokens']] as [$method, $path]) {
            yield "$method $path" => [$method, $path];
        }
    }

    public function testTheApiLeavesTheSessionAlone(): void
    {
        $this->send('GET', '/api/books');
        $this->send('GET', '/api/loans', token: 'amira');
        $this->send('POST', '/api/books', '{"title": "Sula", "author": "Toni Morrison", "year": 1973}', 'jonas');

        self::assertSame(['csrf' => 'the-token'], $this->session->values, 'No cookie, no session: programs use tokens');
    }

    /** @param array<string, string> $query */
    private function send(string $method, string $path, string $body = '', ?string $token = null, array $query = []): Response
    {
        $headers = ['content-type' => 'application/json', ...$token === null ? [] : ['authorization' => 'Bearer ' . $this->tokens[$token]]];

        return $this->app->handle(new Request($method, $path, query: $query, headers: $headers, body: $body));
    }

    /** @return array<mixed> */
    private static function decoded(Response $answer): array
    {
        $data = json_decode($answer->body, true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }
}
