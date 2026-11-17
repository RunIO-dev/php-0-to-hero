<?php

declare(strict_types=1);

// Exercise 62, intermediate: log in.
//
// This form logs in anyone who types a name. Make it check:
// - Log in only when the name is in MEMBERS and password_verify accepts the password. Don't trim
//   the password.
// - On success: a new session ID, then $_SESSION['member'] = the name, then a 303 redirect to /home.php.
// - Otherwise: status 422, the message "Wrong name or password." (the same for a wrong name and a
//   wrong password), and the form again, with the name that was typed, escaped.

require_once __DIR__ . '/account.php';

$name = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkToken();
    $_SESSION['member'] = $name;
    header('Location: /home.php', true, 303);
    exit;
}
?>
<?php if ($error !== null): ?>
<p role="alert"><?= $error ?></p>
<?php endif; ?>
<form method="post" action="/login.php">
    <input type="hidden" name="_token" value="<?= token() ?>">
    <label>Name <input name="name" value="<?= $name ?>" autocomplete="username"></label>
    <label>Password <input type="password" name="password" autocomplete="current-password"></label>
    <button>Log in</button>
</form>
<?php // Check your work: composer exercise 62 intermediate
