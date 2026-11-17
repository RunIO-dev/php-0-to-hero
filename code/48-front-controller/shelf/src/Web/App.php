<?php

declare(strict_types=1);

namespace Shelf\Web;

use InvalidArgumentException;
use Shelf\Library;

use function Shelf\e;

/** Shelf on the web: one request in, one response out. public/index.php sends every request here. */
final readonly class App
{
    private const array FIELDS = ['title', 'author', 'year', 'isbn'];

    public function __construct(private Library $library) {}

    public function handle(Request $request): Response
    {
        $routes = [
            '/' => ['GET' => fn(): Response => $this->list()],
            '/add' => [
                'GET' => fn(): Response => $this->form(array_fill_keys(self::FIELDS, ''), null),
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
        $books = $this->library->books();
        ob_start(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shelf</title>
</head>
<body>
    <h1>Shelf</h1>
    <ul>
<?php foreach ($books as $book): ?>
        <li><?= e($book->title) ?> (<?= e($book->author) ?>, <?= $book->year ?>)<?= $book->isAvailable() ? '' : ' · all copies on loan' ?></li>
<?php endforeach; ?>
    </ul>
    <p><?= count($books) ?> books · <a href="/add">Add a book</a></p>
</body>
</html>
<?php
        return Response::html((string) ob_get_clean());
    }

    private function add(Request $request): Response
    {
        $fields = [];
        foreach (self::FIELDS as $name) {
            $fields[$name] = $request->field($name);
        }
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

    /** @param array<string, string> $fields */
    private function form(array $fields, ?string $error, int $status = 200): Response
    {
        ob_start(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add a book · Shelf</title>
</head>
<body>
    <h1>Add a book</h1>
<?php if ($error !== null): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>
    <form method="post" action="/add">
        <label>Title <input name="title" value="<?= e($fields['title']) ?>" required maxlength="200"></label>
        <label>Author <input name="author" value="<?= e($fields['author']) ?>" required maxlength="200"></label>
        <label>Year <input name="year" value="<?= e($fields['year']) ?>" required inputmode="numeric"></label>
        <label>ISBN <input name="isbn" value="<?= e($fields['isbn']) ?>"></label>
        <button>Add</button>
    </form>
    <p><a href="/">Back to the list</a></p>
</body>
</html>
<?php
        return Response::html((string) ob_get_clean(), $status);
    }
}
