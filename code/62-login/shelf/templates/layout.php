<?php

declare(strict_types=1);

// Shelf's page frame: the bar at the top, the stylesheet, and the page's own content in <main>.
// $content is HTML that another template has rendered, with its values escaped there: the one
// value printed as it is. Chapter 62: the bar shows who is logged in, with a form to log out.

use function Shelf\e;

return static function (string $title, string $content, ?string $member = null, string $token = ''): void { ?>
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
        <nav>
            <a href="/">Books</a> <a class="button" href="/add">Add a book</a>
<?php if ($member === null): ?>
            <a href="/login">Log in</a>
<?php else: ?>
            <form class="who" method="post" action="/logout">
                <input type="hidden" name="_token" value="<?= e($token) ?>">
                <span><?= e($member) ?></span> <button class="link">Log out</button>
            </form>
<?php endif; ?>
        </nav>
    </header>
    <main>
<?= $content ?>
    </main>
</body>
</html>
<?php };
