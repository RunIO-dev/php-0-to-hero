<?php

declare(strict_types=1);

namespace Shelf\Web;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;
use LogicException;
use Psr\Log\LoggerInterface;
use Shelf\Accounts;
use Shelf\ApiTokens;
use Shelf\Book;
use Shelf\BookExists;
use Shelf\BookQuery;
use Shelf\BookNotFound;
use Shelf\Library;
use Shelf\Loan;
use Shelf\Member;

/**
 * Shelf on the web: one request in, one response out. public/index.php sends every request here;
 * the HTML is in templates/ (chapter 49), and what a visitor's pages remember is in the session (50).
 * Every POST must carry the session's CSRF token (51). Covers are uploaded with a new book (52).
 * Each book has its own page, where its copies can be changed and it can be removed (55), unless
 * it is on loan (56). Members log in with their password, and out again (62). Only staff may change
 * books, and every member sees their own loans (63). Logins and refusals go into the log (66).
 * Programs read the books as JSON at /api/books (67), and get every error as problem details (68).
 * They log in with a token, not a session (69): members read their loans, staff add books.
 * The list of books comes a page at a time, and can be filtered (70).
 */
final readonly class App
{
    /** Paths for members only (chapter 63); the ones in STAFF_ONLY need a role that may change books. */
    private const array MEMBERS_ONLY = ['/loans', '/add', '/copies', '/remove'];

    private const array STAFF_ONLY = ['/add', '/copies', '/remove'];

    /** Books per page of /api/books (chapter 70), and the most a program may ask for. */
    private const int PER_PAGE = 20;

    private const int MAX_PER_PAGE = 100;

    private Csrf $csrf;

    public function __construct(
        private Library $library,
        private Accounts $accounts,
        private Templates $templates,
        private Session $session,
        private Covers $covers,
        private ApiTokens $tokens,
        private LoggerInterface $logger,
    ) {
        $this->csrf = new Csrf($session);
    }

    public function handle(Request $request): Response
    {
        $routes = [
            '/' => ['GET' => fn(): Response => $this->list()],
            '/add' => [
                'GET' => fn(): Response => $this->form(['title' => '', 'author' => '', 'year' => '', 'isbn' => ''], null),
                'POST' => fn(): Response => $this->add($request),
            ],
            '/cover' => ['GET' => fn(): Response => $this->cover($request->query('title'))],
            '/book' => ['GET' => fn(): Response => $this->book($request->query('title'))],
            '/copies' => ['POST' => fn(): Response => $this->copies($request)],
            '/remove' => ['POST' => fn(): Response => $this->remove($request)],
            '/login' => [
                'GET' => fn(): Response => $this->loginForm('', null),
                'POST' => fn(): Response => $this->logIn($request),
            ],
            '/logout' => ['POST' => fn(): Response => $this->logOut()],
            '/loans' => ['GET' => fn(): Response => $this->loans()],
            '/api/books' => [
                'GET' => fn(): Response => $this->apiBooks($request),
                'POST' => fn(): Response => $this->apiAdd($request),
            ],
            '/api/tokens' => [
                'POST' => fn(): Response => $this->apiLogIn($request),
                'DELETE' => fn(): Response => $this->apiLogOut($request),
            ],
            '/api/loans' => ['GET' => fn(): Response => $this->apiLoans($request)],
            '/api/book' => ['GET' => fn(): Response => $this->apiBook($request->query('title'))],
        ];
        $route = $routes[$request->path] ?? null;
        $api = str_starts_with($request->path, '/api/'); // programs get errors as problem details (chapter 68)
        if ($route === null) {
            return $api ? Response::problem(404, 'Not Found', "The API has no address $request->path.") : $this->missing();
        }
        if (!isset($route[$request->method])) {
            $allow = implode(', ', array_keys($route));

            return ($api ? Response::problem(405, 'Method Not Allowed', "Use $allow.") : new Response(405))->withHeader('Allow', $allow);
        }
        $refused = $this->refused($request->path);
        if ($refused !== null) {
            return $refused;
        }
        // The API takes no cookies, only tokens, which a browser never sends on its own: no CSRF (chapter 69).
        if ($request->method === 'POST' && !$api && !$this->csrf->isValid($request->field(Csrf::FIELD))) {
            return Response::html($this->page('Expired · Shelf', 'expired', []), 403);
        }

        return $route[$request->method]();
    }

    private function list(): Response
    {
        $flash = $this->session->pull('flash');
        $books = $this->library->books();
        $covered = [];
        foreach ($books as $book) {
            if ($this->covers->find($book->title) !== null) {
                $covered[] = $book->title;
            }
        }

        return Response::html($this->page('Shelf', 'list', [$flash, $covered, ...$books]));
    }

    private function add(Request $request): Response
    {
        $fields = [
            'title' => $request->field('title'),
            'author' => $request->field('author'),
            'year' => $request->field('year'),
            'isbn' => $request->field('isbn'),
        ];
        $book = [$fields['title'], $fields['author'], $fields['year']];
        if ($fields['isbn'] !== '') {
            $book[] = $fields['isbn'];
        }
        $cover = $request->files['cover'] ?? new Upload(UPLOAD_ERR_NO_FILE);
        try {
            if (!$cover->isEmpty()) {
                $this->covers->check($cover); // before the book is added: all or nothing
            }
            $added = $this->library->add($book);
        } catch (InvalidArgumentException $e) {
            return $this->form($fields, $e->getMessage(), 422);
        }
        if (!$cover->isEmpty()) {
            $this->covers->save($added->title, $cover);
        }
        $this->session->set('flash', "Added \"$added->title\".");

        return Response::redirect('/');
    }

    /** One book's page. $error is set when a form comes back with a mistake, and $copies when it was the copies form. */
    private function book(string $title, ?string $copies = null, ?string $error = null): Response
    {
        try {
            $book = $this->library->find($title);
        } catch (BookNotFound) {
            return $this->missing();
        }
        $html = $this->page("$book->title · Shelf", 'book', [
            'book' => $book,
            'hasCover' => $this->covers->find($book->title) !== null,
            'flash' => $this->session->pull('flash'),
            'copies' => $copies ?? (string) $book->copies,
            'error' => $error,
            'token' => $this->csrf->token(),
            'mayChange' => $this->member()?->role->mayChangeBooks() ?? false,
        ]);

        return Response::html($html, $error === null ? 200 : 422);
    }

    private function copies(Request $request): Response
    {
        $title = $request->field('title');
        try {
            $book = $this->library->changeCopies($title, $request->field('copies'));
        } catch (BookNotFound) {
            return $this->missing();
        } catch (InvalidArgumentException $e) {
            return $this->book($title, $request->field('copies'), $e->getMessage());
        }
        $this->session->set('flash', "Saved: the library has $book->copies " . ($book->copies === 1 ? 'copy' : 'copies') . '.');

        return Response::redirect('/book?title=' . urlencode($book->title));
    }

    private function remove(Request $request): Response
    {
        try {
            $book = $this->library->remove($request->field('title'));
        } catch (BookNotFound) {
            return $this->missing();
        } catch (InvalidArgumentException $e) {
            return $this->book($request->field('title'), error: $e->getMessage());
        }
        $this->covers->remove($book->title);
        $this->session->set('flash', "Removed \"$book->title\".");

        return Response::redirect('/');
    }

    private function logIn(Request $request): Response
    {
        $member = $this->accounts->authenticate($request->field('name'), $request->secret('password'));
        if ($member === null) {
            $this->logger->warning('Failed login for {name}', ['name' => $request->field('name')]); // never the password

            return $this->loginForm($request->field('name'), 'Wrong name or password.', 422);
        }
        $this->logger->info('{name} logged in', ['name' => $member->name]);
        $this->session->regenerate(); // who the visitor is changes, so the session ID does too
        $this->session->set('member', $member->name);
        $this->session->set('flash', "Welcome, $member->name.");

        return Response::redirect('/');
    }

    private function logOut(): Response
    {
        $this->session->pull('member');
        $this->session->regenerate();
        $this->session->set('flash', 'You are logged out.');

        return Response::redirect('/');
    }

    private function loginForm(string $name, ?string $error, int $status = 200): Response
    {
        $html = $this->page('Log in · Shelf', 'login', ['name' => $name, 'error' => $error, 'token' => $this->csrf->token(), 'flash' => $this->session->pull('flash')]);

        return Response::html($html, $status);
    }

    private function loans(): Response
    {
        $member = $this->member() ?? throw new LogicException('refused() lets only members in');

        return Response::html($this->page('My loans · Shelf', 'loans', $this->library->loansOf($member->name)));
    }

    /**
     * The books, for programs: the same data as books.json, and where each cover is. A page at a time,
     * ?page=2&per_page=20, filtered by ?author= and ?available=true (chapter 70). The body stays a list,
     * as in chapter 67, so programs written for it keep working; the Link header names the other pages.
     */
    private function apiBooks(Request $request): Response
    {
        $page = self::number($request, 'page', 1);
        $perPage = self::number($request, 'per_page', self::PER_PAGE);
        $available = $request->query('available');
        if ($page === null || $page < 1 || $perPage === null || $perPage < 1 || $perPage > self::MAX_PER_PAGE
            || !in_array($available, ['', 'true', 'false'], true)) {
            $detail = 'page is a number from 1, per_page a number from 1 to ' . self::MAX_PER_PAGE . ', available is true or false.';

            return Response::problem(400, 'Bad Request', $detail);
        }
        $query = new BookQuery($request->query('author'), $available === 'true');
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

        return Response::json(array_map($this->bookData(...), $found['books']))->withHeader('Link', implode(', ', $links));
    }

    /** A whole number from the query, $default when it wasn't sent, or null when it isn't a number. */
    private static function number(Request $request, string $name, int $default): ?int
    {
        $value = $request->query($name);
        if (!isset($request->query[$name])) {
            return $default;
        }

        return preg_match('/^[0-9]{1,6}$/D', $value) === 1 ? (int) $value : null;
    }

    /** One book, for programs (chapter 68), found by its title in any case. */
    private function apiBook(string $title): Response
    {
        try {
            return Response::json($this->bookData($this->library->find($title)));
        } catch (BookNotFound) {
            return Response::problem(404, 'Not Found', "There is no book called \"$title\".");
        }
    }

    /** A token for a program, for the name and password in the JSON body (chapter 69). */
    private function apiLogIn(Request $request): Response
    {
        $sent = $this->jsonBody($request);
        if ($sent instanceof Response) {
            return $sent;
        }
        $name = is_string($sent['name'] ?? null) ? trim($sent['name']) : '';
        $member = $this->accounts->authenticate($name, is_string($sent['password'] ?? null) ? $sent['password'] : '');
        if ($member === null) {
            $this->logger->warning('Failed API login for {name}', ['name' => $name]);

            return Response::problem(401, 'Unauthorized', 'Wrong name or password.')->withHeader('WWW-Authenticate', 'Bearer');
        }
        $this->logger->info('{name} got an API token', ['name' => $member->name]);
        $issued = $this->tokens->issue($member->name, new DateTimeImmutable());

        // expires_in: seconds, as OAuth names it; a program renews the token before then.
        return Response::json(['token' => $issued['token'], 'expires_in' => ApiTokens::DAYS * 24 * 60 * 60], 201);
    }

    /** The token that came with the request ends now. */
    private function apiLogOut(Request $request): Response
    {
        $token = $request->bearerToken();
        if ($token === null || $this->caller($request) === null) {
            return self::unauthorized();
        }
        $this->tokens->revoke($token);

        return new Response(204);
    }

    /** The loans of the member whose token came with the request. */
    private function apiLoans(Request $request): Response
    {
        $member = $this->caller($request);
        if ($member === null) {
            return self::unauthorized();
        }
        $loans = array_map(
            static fn(Loan $loan): array => ['title' => $loan->book->title, 'due' => $loan->due->format('Y-m-d')],
            $this->library->loansOf($member->name),
        );

        return Response::json($loans);
    }

    /** A new book, from JSON: staff only, as on the website. */
    private function apiAdd(Request $request): Response
    {
        $member = $this->caller($request);
        if ($member === null) {
            return self::unauthorized();
        }
        if (!$member->role->mayChangeBooks()) {
            $this->logger->warning('{name} was refused {path}', ['name' => $member->name, 'path' => 'POST /api/books']);

            return Response::problem(403, 'Forbidden', 'Only staff may add books.');
        }
        $sent = $this->jsonBody($request);
        if ($sent instanceof Response) {
            return $sent;
        }
        $isbn = $sent['isbn'] ?? null;
        if (!is_string($sent['title'] ?? null) || !is_string($sent['author'] ?? null) || !is_int($sent['year'] ?? null)
            || !($isbn === null || is_string($isbn))) {
            return Response::problem(422, 'Unprocessable Content', 'A book has a title and an author as text, a year as a number, and maybe an ISBN as text.');
        }
        try {
            $book = $this->library->add([$sent['title'], $sent['author'], (string) $sent['year'], ...$isbn === null ? [] : [$isbn]]);
        } catch (BookExists $e) {
            return Response::problem(409, 'Conflict', $e->getMessage()); // chapter 71: the contract test found it was 422
        } catch (InvalidArgumentException $e) {
            return Response::problem(422, 'Unprocessable Content', $e->getMessage());
        }

        return Response::json($this->bookData($book), 201)->withHeader('Location', '/api/book?title=' . rawurlencode($book->title));
    }

    /**
     * The request's JSON object, or the problem to answer: 415 when it isn't JSON, 400 when it can't be read.
     *
     * @return array<mixed>|Response
     */
    private function jsonBody(Request $request): array|Response
    {
        if (!$request->isJson()) {
            return Response::problem(415, 'Unsupported Media Type', 'Send JSON, with Content-Type: application/json.');
        }
        try {
            $sent = $request->json();
        } catch (JsonException) {
            return Response::problem(400, 'Bad Request', 'The body is not valid JSON.');
        }

        return is_array($sent) ? $sent : Response::problem(400, 'Bad Request', 'The body must be a JSON object.');
    }

    /** The member whose token came with the request: never the session, which the API doesn't use. */
    private function caller(Request $request): ?Member
    {
        $token = $request->bearerToken();

        return $token === null ? null : $this->tokens->member($token, new DateTimeImmutable());
    }

    private static function unauthorized(): Response
    {
        return Response::problem(401, 'Unauthorized', 'Send a valid token: Authorization: Bearer <token>.')->withHeader('WWW-Authenticate', 'Bearer');
    }

    /** @return array<string, mixed> */
    private function bookData(Book $book): array
    {
        $cover = $this->covers->find($book->title) === null ? null : '/cover?title=' . rawurlencode($book->title);

        return [...$book->toArray(), 'cover' => $cover];
    }

    /**
     * Whether this path is closed to the visitor: a visitor who isn't logged in is sent to the login,
     * a member without the role gets 403. Checked for every request, before the route runs.
     */
    private function refused(string $path): ?Response
    {
        if (!in_array($path, self::MEMBERS_ONLY, true)) {
            return null;
        }
        $member = $this->member();
        if ($member === null) {
            $this->session->set('flash', 'Log in first.');

            return Response::redirect('/login');
        }
        if (in_array($path, self::STAFF_ONLY, true) && !$member->role->mayChangeBooks()) {
            $this->logger->warning('{name} was refused {path}', ['name' => $member->name, 'path' => $path]);

            return Response::html($this->page('Not allowed · Shelf', 'forbidden', []), 403);
        }

        return null;
    }

    /**
     * The member who is logged in, read again on every request: a member who was removed, or lost
     * a role, loses it at once.
     */
    private function member(): ?Member
    {
        $name = $this->session->get('member');

        return $name === null ? null : $this->library->findMember($name);
    }

    /**
     * A whole page, with the bar that shows who is logged in. The token for the logout form is
     * made only for a member: a visitor who only reads still gets no session cookie.
     *
     * @param array<mixed> $data the template's arguments
     */
    private function page(string $title, string $name, array $data): string
    {
        $member = $this->member();

        return $this->templates->page($title, $name, $data, ['member' => $member, 'token' => $member === null ? '' : $this->csrf->token()]);
    }

    private function missing(): Response
    {
        return Response::html($this->page('Not found · Shelf', 'missing', []), 404);
    }

    private function cover(string $title): Response
    {
        $cover = $this->covers->find($title);
        $image = $cover === null ? false : file_get_contents($cover['path']);
        if ($cover === null || $image === false) {
            return $this->missing();
        }

        // nosniff: the browser must take the file as the image the Content-Type says, never as a page.
        return new Response(200, ['Content-Type' => $cover['type'], 'X-Content-Type-Options' => 'nosniff'], $image);
    }

    /** @param array{title: string, author: string, year: string, isbn: string} $fields */
    private function form(array $fields, ?string $error, int $status = 200): Response
    {
        $html = $this->page('Add a book · Shelf', 'add', [...$fields, 'error' => $error, 'token' => $this->csrf->token()]);

        return Response::html($html, $status);
    }
}
