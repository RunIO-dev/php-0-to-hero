<?php

declare(strict_types=1);

// The login form (chapter 62), empty or with the name that was typed. The password never comes back.
// Chapter 63: a message shown once, such as why the visitor was sent here.

use function Shelf\e;

return static function (string $name, ?string $error, string $token, ?string $flash = null): void { ?>
    <h1>Log in</h1>
<?php if ($flash !== null): ?>
    <p class="flash" role="status"><?= e($flash) ?></p>
<?php endif; ?>
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
