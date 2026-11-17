<?php

declare(strict_types=1);

// Shelf's page frame: the bar at the top, the stylesheet, and the page's own content in <main>.
// $content is HTML that another template has rendered, with its values escaped there: the one
// value printed as it is.

use function Shelf\e;

return static function (string $title, string $content): void { ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <header class="site">
        <a class="brand" href="/">Shelf</a>
        <nav><a href="/">Books</a> <a class="button" href="/add">Add a book</a></nav>
    </header>
    <main>
<?= $content ?>
    </main>
</body>
</html>
<?php };
