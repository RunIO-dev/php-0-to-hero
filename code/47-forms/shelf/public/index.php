<?php

declare(strict_types=1);

// Shelf on the web (chapters 46-47): the book list, and a link to add a book. Every text from
// the data is escaped with e().

use Shelf\JsonBookRepository;
use Shelf\Library;

use function Shelf\e;
use function Shelf\loadMembers;

require __DIR__ . '/../vendor/autoload.php';

$library = new Library(new JsonBookRepository(__DIR__ . '/../books.json'), loadMembers(__DIR__ . '/../members.json'));
$books = $library->books();
?>
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
    <p><?= count($books) ?> books · <a href="/add.php">Add a book</a></p>
</body>
</html>
