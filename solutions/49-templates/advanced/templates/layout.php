<?php

declare(strict_types=1);

return static function (string $title, string $content): void { ?>
<title><?= htmlspecialchars($title) ?></title>
<main><?= $content ?></main>
<?php };
