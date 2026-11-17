<?php

declare(strict_types=1);

// Exercise 49, beginner: a member's card as a template.

return static function (string $name, string $role, int $loans): void { ?>
<div class="card"><h2><?= htmlspecialchars($name) ?></h2><p><?= htmlspecialchars($role) ?> · <?= $loans ?> loans</p></div>
<?php };
