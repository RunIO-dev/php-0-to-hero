<?php

declare(strict_types=1);

// One book's page (chapter 55): its details, a form for how many copies the library has, and one
// to remove it. $copies is the form's value; $error, if set, says what was wrong with a form. Chapter 56:
// the message is above both forms, as removing a book on loan can be refused too. Chapter 63: the
// forms only for a member who may change books.

use Shelf\Book;

use function Shelf\e;

return static function (Book $book, bool $hasCover, ?string $flash, string $copies, ?string $error, string $token, bool $mayChange = false): void { ?>
    <p class="back"><a href="/">← All books</a></p>
    <h1><?= e($book->title) ?></h1>
<?php if ($flash !== null): ?>
    <p class="flash" role="status"><?= e($flash) ?></p>
<?php endif; ?>
<?php if ($error !== null): ?>
    <p class="error" role="alert"><?= e($error) ?></p>
<?php endif; ?>
    <div class="card details">
<?php if ($hasCover): ?>
        <img class="cover" src="/cover?title=<?= e(urlencode($book->title)) ?>" alt="">
<?php else: ?>
        <span class="cover" aria-hidden="true"><?= e(mb_substr($book->title, 0, 1)) ?></span>
<?php endif; ?>
        <div>
            <p><?= e($book->author) ?>, <?= $book->year ?></p>
<?php if ($book->isbn !== null): ?>
            <p>ISBN <?= e($book->isbn->digits) ?></p>
<?php endif; ?>
        </div>
    </div>
<?php if ($mayChange): ?>
    <form class="card" method="post" action="/copies">
        <input type="hidden" name="_token" value="<?= e($token) ?>">
        <input type="hidden" name="title" value="<?= e($book->title) ?>">
        <label>Copies in the library <input name="copies" value="<?= e($copies) ?>" required inputmode="numeric"></label>
        <p class="actions"><button>Save</button></p>
    </form>
    <form class="card" method="post" action="/remove">
        <input type="hidden" name="_token" value="<?= e($token) ?>">
        <input type="hidden" name="title" value="<?= e($book->title) ?>">
        <p>Removing a book takes it off the shelf for good, with its cover.</p>
        <p class="actions"><button class="danger">Remove this book</button></p>
    </form>
<?php endif; ?>
<?php };
