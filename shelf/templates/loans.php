<?php

declare(strict_types=1);

// The logged-in member's own loans (chapter 63), one Loan per argument.

use Shelf\Loan;

use function Shelf\e;

return static function (Loan ...$loans): void { ?>
    <h1>My loans</h1>
<?php if ($loans === []): ?>
    <p class="empty">You have no books on loan.</p>
<?php else: ?>
    <ul class="loans">
<?php foreach ($loans as $loan): ?>
        <li><a href="/book?title=<?= e(urlencode($loan->book->title)) ?>"><?= e($loan->book->title) ?></a>, due back on <?= e($loan->due->format('l, j F Y')) ?></li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php };
