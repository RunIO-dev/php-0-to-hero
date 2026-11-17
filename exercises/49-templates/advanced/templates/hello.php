<?php

declare(strict_types=1);

return static function (string $name): void { ?>
<p>Hello, <?= htmlspecialchars($name) ?>!</p>
<?php };
