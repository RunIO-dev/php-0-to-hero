<?php

declare(strict_types=1);

// Logging in: check the name and the password, give the session a new ID, then remember the member.

require_once __DIR__ . '/account.php';

$name = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkToken();
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : ''; // not trimmed: spaces count
    $hash = MEMBERS[$name] ?? null;
    if ($hash !== null && password_verify($password, $hash)) {
        session_regenerate_id(true);
        $_SESSION['member'] = $name;
        header('Location: /home.php', true, 303);
        exit;
    }
    http_response_code(422);
    $error = 'Wrong name or password.';
}
?>
<?php if ($error !== null): ?>
<p role="alert"><?= $error ?></p>
<?php endif; ?>
<form method="post" action="/login.php">
    <input type="hidden" name="_token" value="<?= token() ?>">
    <label>Name <input name="name" value="<?= htmlspecialchars($name, ENT_QUOTES) ?>" autocomplete="username"></label>
    <label>Password <input type="password" name="password" autocomplete="current-password"></label>
    <button>Log in</button>
</form>
