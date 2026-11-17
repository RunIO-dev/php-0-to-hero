<?php

declare(strict_types=1);

// The book list as cards: a message shown once, or null; the titles that have a cover; one Book per argument. A book without a cover shows its first letter.

use Shelf\Book;

use function Shelf\e;

return static function (?string $flash, array $covered, Book ...$books): void { ?>
    <h1>Books</h1>
<?php if ($flash !== null): ?>
    <p class="flash" role="status"><?= e($flash) ?></p>
<?php endif; ?>
<?php if ($books === []): ?>
    <p class="empty">No books yet. <a href="/add">Add the first one.</a></p>
<?php else: ?>
    <ul class="books">
<?php foreach ($books as $book): ?>
        <li class="book">
<?php if (in_array($book->title, $covered, true)): ?>
            <img class="cover" src="/cover?title=<?= e(urlencode($book->title)) ?>" alt="">
<?php else: ?>
            <span class="cover" aria-hidden="true"><?= e(mb_substr($book->title, 0, 1)) ?></span>
<?php endif; ?>
            <h2><?= e($book->title) ?></h2>
            <p><?= e($book->author) ?>, <?= $book->year ?></p>
<?php if ($book->isAvailable()): ?>
            <p class="status">Available</p>
<?php else: ?>
            <p class="status on-loan">All copies on loan</p>
<?php endif; ?>
        </li>
<?php endforeach; ?>
    </ul>
    <p class="count"><?= count($books) ?> books</p>
<?php endif; ?>
<?php };
