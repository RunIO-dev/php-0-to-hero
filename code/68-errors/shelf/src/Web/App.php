<?php

declare(strict_types=1);

namespace Shelf\Web;

use InvalidArgumentException;
use LogicException;
use Psr\Log\LoggerInterface;
use Shelf\Accounts;
use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\Library;
use Shelf\Member;

/**
 * Shelf on the web: one request in, one response out. public/index.php sends every request here;
 * the HTML is in templates/ (chapter 49), and what a visitor's pages remember is in the session (50).
 * Every POST must carry the session's CSRF token (51). Covers are uploaded with a new book (52).
 * Each book has its own page, where its copies can be changed and it can be removed (55), unless
 * it is on loan (56). Members log in with their password, and out again (62). Only staff may change
 * books, and every member sees their own loans (63). Logins and refusals go into the log (66).
 * Programs read the books as JSON at /api/books (67), and get every error as problem details (68).
 */
final readonly class App
{
    /** Paths for members only (chapter 63); the ones in STAFF_ONLY need a role that may change books. */
    private const array MEMBERS_ONLY = ['/loans', '/add', '/copies', '/remove'];

    private const array STAFF_ONLY = ['/add', '/copies', '/remove'];

    private Csrf $csrf;

    public function __construct(
        private Library $library,
        private Accounts $accounts,
        private Templates $templates,
        private Session $session,
        private Covers $covers,
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
            '/api/books' => ['GET' => fn(): Response => $this->apiBooks()],
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
        if ($request->method === 'POST' && !$this->csrf->isValid($request->field(Csrf::FIELD))) {
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

    /** The books, for programs: the same data as books.json, and where each cover is. */
    private function apiBooks(): Response
    {
        return Response::json(array_map($this->bookData(...), $this->library->books()));
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
