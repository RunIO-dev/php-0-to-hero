<?php

declare(strict_types=1);

// Exercise 49, intermediate: a table template.

return static function (string $caption, string ...$titles): void { ?>
<?php if ($titles === []): ?>
<p class="empty">No books yet.</p>
<?php else: ?>
<table><caption><?= htmlspecialchars($caption) ?></caption>
<?php foreach ($titles as $title): ?>
<tr><td><?= htmlspecialchars($title) ?></td></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<?php };
