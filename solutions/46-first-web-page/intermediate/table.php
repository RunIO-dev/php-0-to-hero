<?php

declare(strict_types=1);

// Exercise 46, intermediate: a table of books. A solution.

const BOOKS = [
    ['title' => 'Tom & Jerry', 'year' => 1940],
    ['title' => 'The "Quiet" American', 'year' => 1955],
    ['title' => "Butler's <i>Kindred</i>", 'year' => 1979],
];
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Books</title></head>
<body>
    <table>
<?php foreach (BOOKS as $book): ?>
        <tr title="<?= htmlspecialchars($book['title']) ?>"><td><?= htmlspecialchars($book['title']) ?></td><td><?= $book['year'] ?></td></tr>
<?php endforeach; ?>
    </table>
</body>
</html>
