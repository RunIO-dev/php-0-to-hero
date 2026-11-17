<?php

declare(strict_types=1);

namespace Shelf\Controller;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;
use Psr\Log\LoggerInterface;
use Shelf\Accounts;
use Shelf\ApiTokens;
use Shelf\Book;
use Shelf\BookExists;
use Shelf\BookNotFound;
use Shelf\BookQuery;
use Shelf\Library;
use Shelf\Loan;
use Shelf\Member;
use Shelf\Form\BookData;
use Shelf\Web\Covers;
use Shelf\Web\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Shelf's API (chapters 67 to 71), from App's routes, for programs: JSON in and out, errors as problem
 * details, and tokens instead of the session. Errors that end in an exception, such as an unknown
 * address, are answered by ErrorPages. Chapter 76 hands the tokens to Symfony's security.
 */
#[AsController]
#[Route('/api', name: 'api_')]
final readonly class ApiController
{
    /** Books per page of /api/books (chapter 70), and the most a program may ask for. */
    private const int PER_PAGE = 20;

    private const int MAX_PER_PAGE = 100;

    public function __construct(
        private Library $library,
        private Accounts $accounts,
        private ApiTokens $tokens,
        private Covers $covers,
        private LoggerInterface $logger,
        private ValidatorInterface $validator,
    ) {}

    /**
     * The books, a page at a time, filtered by ?author= and ?available=true (chapter 70). The body stays
     * a list, as in chapter 67; the Link header names the other pages.
     */
    #[Route('/books', name: 'books', methods: ['GET'])]
    public function books(Request $request, #[MapQueryParameter] string $author = '', #[MapQueryParameter] string $available = ''): Response
    {
        $page = self::number($request, 'page', 1);
        $perPage = self::number($request, 'per_page', self::PER_PAGE);
        if ($page === null || $page < 1 || $perPage === null || $perPage < 1 || $perPage > self::MAX_PER_PAGE
            || !in_array($available, ['', 'true', 'false'], true)) {
            $detail = 'page is a number from 1, per_page a number from 1 to ' . self::MAX_PER_PAGE . ', available is true or false.';

            return Json::problem(400, 'Bad Request', $detail);
        }
        $query = new BookQuery(trim($author), $available === 'true');
        $found = $this->library->page($query, $page, $perPage);

        $link = static fn(int $to, string $rel): string => '</api/books?'
            . http_build_query(array_filter(['author' => $query->author, 'available' => $available]) + ['page' => $to, 'per_page' => $perPage])
            . ">; rel=\"$rel\"";
        $links = [$link(1, 'first')];
        if ($page > 1) {
            $links[] = $link(min($page - 1, $found['pages']), 'prev');
        }
        if ($page < $found['pages']) {
            $links[] = $link($page + 1, 'next');
        }
        $links[] = $link($found['pages'], 'last');

        return Json::response(array_map($this->bookData(...), $found['books']), headers: ['Link' => implode(', ', $links)]);
    }

    /** One book (chapter 68), found by its title in any case. */
    #[Route('/book', name: 'book', methods: ['GET'])]
    public function book(#[MapQueryParameter] string $title = ''): Response
    {
        $title = trim($title);
        try {
            return Json::response($this->bookData($this->library->find($title)));
        } catch (BookNotFound) {
            return Json::problem(404, 'Not Found', "There is no book called \"$title\".");
        }
    }

    /** A new book, from JSON: staff only, as on the website (chapter 69), with the form's rules (75). */
    #[Route('/books', name: 'add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $member = $this->caller($request);
        if ($member === null) {
            return self::unauthorized();
        }
        if (!$member->role->mayChangeBooks()) {
            $this->logger->warning('{name} was refused {path}', ['name' => $member->name, 'path' => 'POST /api/books']);

            return Json::problem(403, 'Forbidden', 'Only staff may add books.');
        }
        $sent = self::jsonBody($request);
        if ($sent instanceof Response) {
            return $sent;
        }
        $checked = self::bookFrom($sent);
        $errors = $checked['errors'];
        foreach ($this->validator->validate($checked['data']) as $violation) {
            if (!in_array($violation->getPropertyPath(), array_column($errors, 'field'), true)) { // one mistake per field
                $errors[] = ['field' => $violation->getPropertyPath(), 'message' => (string) $violation->getMessage()];
            }
        }
        if ($errors !== []) {
            return Json::problem(422, 'Unprocessable Content', 'The book has mistakes: errors names each one.', '/problems/invalid-book', ['errors' => $errors]);
        }
        try {
            $book = $this->library->add($checked['data']->fields());
        } catch (BookExists $e) {
            return Json::problem(409, 'Conflict', $e->getMessage());
        } catch (InvalidArgumentException $e) {
            return Json::problem(422, 'Unprocessable Content', $e->getMessage());
        }

        return Json::response($this->bookData($book), 201, ['Location' => '/api/book?title=' . rawurlencode($book->title)]);
    }

    /** A token for a program, for the name and password in the JSON body (chapter 69). */
    #[Route('/tokens', name: 'login', methods: ['POST'])]
    public function logIn(Request $request): Response
    {
        $sent = self::jsonBody($request);
        if ($sent instanceof Response) {
            return $sent;
        }
        $name = is_string($sent['name'] ?? null) ? trim($sent['name']) : '';
        $member = $this->accounts->authenticate($name, is_string($sent['password'] ?? null) ? $sent['password'] : '');
        if ($member === null) {
            $this->logger->warning('Failed API login for {name}', ['name' => $name]);

            return Json::problem(401, 'Unauthorized', 'Wrong name or password.', headers: ['WWW-Authenticate' => 'Bearer']);
        }
        $this->logger->info('{name} got an API token', ['name' => $member->name]);
        $issued = $this->tokens->issue($member->name, new DateTimeImmutable());

        // expires_in: seconds, as OAuth names it; a program renews the token before then.
        return Json::response(['token' => $issued['token'], 'expires_in' => ApiTokens::DAYS * 24 * 60 * 60], 201);
    }

    /** The token that came with the request ends now. */
    #[Route('/tokens', name: 'logout', methods: ['DELETE'])]
    public function logOut(Request $request): Response
    {
        $token = self::bearerToken($request);
        if ($token === null || $this->caller($request) === null) {
            return self::unauthorized();
        }
        $this->tokens->revoke($token);

        return new Response(status: 204);
    }

    /** The loans of the member whose token came with the request. */
    #[Route('/loans', name: 'loans', methods: ['GET'])]
    public function loans(Request $request): Response
    {
        $member = $this->caller($request);
        if ($member === null) {
            return self::unauthorized();
        }
        $loans = array_map(
            static fn(Loan $loan): array => ['title' => $loan->book->title, 'due' => $loan->due->format('Y-m-d')],
            $this->library->loansOf($member->name),
        );

        return Json::response($loans);
    }

    /**
     * The JSON's fields as BookData (chapter 75), and the fields of the wrong JSON type: text where
     * a number belongs, or the other way round. The validator checks the rest.
     *
     * @param array<mixed> $sent
     * @return array{data: BookData, errors: list<array{field: string, message: string}>}
     */
    private static function bookFrom(array $sent): array
    {
        $data = new BookData();
        $errors = [];
        $text = static function (string $field) use ($sent, &$errors): ?string {
            $value = $sent[$field] ?? null;
            if ($value !== null && !is_string($value)) {
                $errors[] = ['field' => $field, 'message' => "$field is text."];
            }

            return is_string($value) ? $value : null;
        };
        $data->title = $text('title');
        $data->author = $text('author');
        $data->isbn = $text('isbn');
        $year = $sent['year'] ?? null;
        if ($year !== null && !is_int($year)) {
            $errors[] = ['field' => 'year', 'message' => 'The year is a number, such as 1979.'];
        }
        $data->year = is_int($year) ? $year : null;

        return ['data' => $data, 'errors' => $errors];
    }

    /** A whole number from the query, $default when it wasn't sent, or null when it isn't a number. */
    private static function number(Request $request, string $name, int $default): ?int
    {
        if (!$request->query->has($name)) {
            return $default;
        }

        return preg_match('/^[0-9]{1,6}$/D', trim($request->query->getString($name))) === 1 ? (int) $request->query->getString($name) : null;
    }

    /**
     * The request's JSON object, or the problem to answer: 415 when it isn't JSON, 400 when it can't be read.
     *
     * @return array<mixed>|Response
     */
    private static function jsonBody(Request $request): array|Response
    {
        if ($request->getContentTypeFormat() !== 'json') {
            return Json::problem(415, 'Unsupported Media Type', 'Send JSON, with Content-Type: application/json.');
        }
        try {
            $sent = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return Json::problem(400, 'Bad Request', 'The body is not valid JSON.');
        }

        return is_array($sent) ? $sent : Json::problem(400, 'Bad Request', 'The body must be a JSON object.');
    }

    /** The token in "Authorization: Bearer <token>", or null when there is none. */
    private static function bearerToken(Request $request): ?string
    {
        return preg_match('/^Bearer +(\S+)$/Di', trim((string) $request->headers->get('Authorization')), $m) === 1 ? $m[1] : null;
    }

    /** The member whose token came with the request: never the session, which the API doesn't use. */
    private function caller(Request $request): ?Member
    {
        $token = self::bearerToken($request);

        return $token === null ? null : $this->tokens->member($token, new DateTimeImmutable());
    }

    private static function unauthorized(): Response
    {
        return Json::problem(401, 'Unauthorized', 'Send a valid token: Authorization: Bearer <token>.', headers: ['WWW-Authenticate' => 'Bearer']);
    }

    /** @return array<string, mixed> */
    private function bookData(Book $book): array
    {
        $cover = $this->covers->find($book->title) === null ? null : '/cover?title=' . rawurlencode($book->title);

        return [...$book->toArray(), 'cover' => $cover];
    }
}
