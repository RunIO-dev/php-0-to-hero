<?php

declare(strict_types=1);

// A template: a file that returns a function. Its parameters are everything the HTML needs,
// with their types. It prints HTML, with every value escaped.

return function (string $title, string $author, int $year): void { ?>
<article>
    <h2><?= htmlspecialchars($title) ?></h2>
    <p><?= htmlspecialchars($author) ?>, <?= $year ?></p>
</article>
<?php };
