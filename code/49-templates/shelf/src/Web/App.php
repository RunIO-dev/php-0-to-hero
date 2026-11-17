<?php

declare(strict_types=1);

namespace Shelf\Web;

use InvalidArgumentException;
use Shelf\Library;

/**
 * Shelf on the web: one request in, one response out. public/index.php sends every request here;
 * the HTML is in templates/ (chapter 49).
 */
final readonly class App
{
    public function __construct(private Library $library, private Templates $templates) {}

    public function handle(Request $request): Response
    {
        $routes = [
            '/' => ['GET' => fn(): Response => $this->list()],
            '/add' => [
                'GET' => fn(): Response => $this->form(['title' => '', 'author' => '', 'year' => '', 'isbn' => ''], null),
                'POST' => fn(): Response => $this->add($request),
            ],
        ];
        $route = $routes[$request->path] ?? null;
        if ($route === null) {
            return Response::html('<h1>Not found</h1>', 404);
        }
        if (!isset($route[$request->method])) {
            return new Response(405, ['Allow' => implode(', ', array_keys($route))]);
        }

        return $route[$request->method]();
    }

    private function list(): Response
    {
        return Response::html($this->templates->page('Shelf', 'list', $this->library->books()));
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
        try {
            $this->library->add($book);
        } catch (InvalidArgumentException $e) {
            return $this->form($fields, $e->getMessage(), 422);
        }

        return Response::redirect('/');
    }

    /** @param array{title: string, author: string, year: string, isbn: string} $fields */
    private function form(array $fields, ?string $error, int $status = 200): Response
    {
        $html = $this->templates->page('Add a book · Shelf', 'add', [...$fields, 'error' => $error]);

        return Response::html($html, $status);
    }
}
