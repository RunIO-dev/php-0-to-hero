<?php

declare(strict_types=1);

// Shelf on the web (chapter 47): add a book. The form posts to itself; Library::add checks the
// data, as it does for the command line. A mistake: 422 and the form again, with what was typed.
// Success: 303 to the list, so that reloading the page doesn't add the book twice.

use Shelf\JsonBookRepository;
use Shelf\Library;

use function Shelf\e;
use function Shelf\loadMembers;

require __DIR__ . '/../vendor/autoload.php';

$library = new Library(new JsonBookRepository(__DIR__ . '/../books.json'), loadMembers(__DIR__ . '/../members.json'));

$fields = ['title' => '', 'author' => '', 'year' => '', 'isbn' => ''];
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($fields) as $name) {
        $value = $_POST[$name] ?? '';
        $fields[$name] = is_string($value) ? trim($value) : '';
    }
    $book = [$fields['title'], $fields['author'], $fields['year']];
    if ($fields['isbn'] !== '') {
        $book[] = $fields['isbn'];
    }
    try {
        $library->add($book);
        header('Location: /', true, 303);
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
        http_response_code(422);
    }
}
?>
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
    <form method="post" action="/add.php">
        <label>Title <input name="title" value="<?= e($fields['title']) ?>" required maxlength="200"></label>
        <label>Author <input name="author" value="<?= e($fields['author']) ?>" required maxlength="200"></label>
        <label>Year <input name="year" value="<?= e($fields['year']) ?>" required inputmode="numeric"></label>
        <label>ISBN <input name="isbn" value="<?= e($fields['isbn']) ?>"></label>
        <button>Add</button>
    </form>
    <p><a href="/">Back to the list</a></p>
</body>
</html>
