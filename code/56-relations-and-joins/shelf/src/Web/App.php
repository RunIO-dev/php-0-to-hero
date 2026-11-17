<?php

declare(strict_types=1);

namespace Shelf\Web;

use InvalidArgumentException;
use Shelf\BookNotFound;
use Shelf\Library;

/**
 * Shelf on the web: one request in, one response out. public/index.php sends every request here;
 * the HTML is in templates/ (chapter 49), and what a visitor's pages remember is in the session (50).
 * Every POST must carry the session's CSRF token (51). Covers are uploaded with a new book (52).
 * Each book has its own page, where its copies can be changed and it can be removed (55), unless
 * it is on loan (56).
 */
final readonly class App
{
    private Csrf $csrf;

    public function __construct(
        private Library $library,
        private Templates $templates,
        private Session $session,
        private Covers $covers,
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
        ];
        $route = $routes[$request->path] ?? null;
        if ($route === null) {
            return $this->missing();
        }
        if (!isset($route[$request->method])) {
            return new Response(405, ['Allow' => implode(', ', array_keys($route))]);
        }
        if ($request->method === 'POST' && !$this->csrf->isValid($request->field(Csrf::FIELD))) {
            return Response::html($this->templates->page('Expired · Shelf', 'expired', []), 403);
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

        return Response::html($this->templates->page('Shelf', 'list', [$flash, $covered, ...$books]));
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
        $html = $this->templates->page("$book->title · Shelf", 'book', [
            'book' => $book,
            'hasCover' => $this->covers->find($book->title) !== null,
            'flash' => $this->session->pull('flash'),
            'copies' => $copies ?? (string) $book->copies,
            'error' => $error,
            'token' => $this->csrf->token(),
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

    private function missing(): Response
    {
        return Response::html($this->templates->page('Not found · Shelf', 'missing', []), 404);
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
        $html = $this->templates->page('Add a book · Shelf', 'add', [...$fields, 'error' => $error, 'token' => $this->csrf->token()]);

        return Response::html($html, $status);
    }
}
