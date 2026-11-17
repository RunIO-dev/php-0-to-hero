<?php

declare(strict_types=1);

// The form to add a book, empty or with what was typed and the reason it was refused.

use function Shelf\e;

return static function (string $title, string $author, string $year, string $isbn, ?string $error): void { ?>
    <h1>Add a book</h1>
<?php if ($error !== null): ?>
    <p class="error" role="alert"><?= e($error) ?></p>
<?php endif; ?>
    <form class="card" method="post" action="/add">
        <label>Title <input name="title" value="<?= e($title) ?>" required maxlength="200"></label>
        <label>Author <input name="author" value="<?= e($author) ?>" required maxlength="200"></label>
        <label>Year <input name="year" value="<?= e($year) ?>" required inputmode="numeric"></label>
        <label>ISBN <small>optional</small> <input name="isbn" value="<?= e($isbn) ?>"></label>
        <p class="actions"><button>Add the book</button> <a href="/">Cancel</a></p>
    </form>
<?php };
