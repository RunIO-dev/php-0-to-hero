<?php

declare(strict_types=1);

// The login form (chapter 62), empty or with the name that was typed. The password never comes back.

use function Shelf\e;

return static function (string $name, ?string $error, string $token): void { ?>
    <h1>Log in</h1>
<?php if ($error !== null): ?>
    <p class="error" role="alert"><?= e($error) ?></p>
<?php endif; ?>
    <form class="card narrow" method="post" action="/login">
        <input type="hidden" name="_token" value="<?= e($token) ?>">
        <label>Name <input name="name" value="<?= e($name) ?>" required autocomplete="username"></label>
        <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
        <p class="actions"><button>Log in</button></p>
    </form>
<?php };
