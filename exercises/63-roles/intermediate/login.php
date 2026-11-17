<?php

declare(strict_types=1);

// The login of chapter 62, with the members of account.php.

require_once __DIR__ . '/account.php';

$name = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkToken();
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if (isset(MEMBERS[$name]) && password_verify($password, MEMBERS[$name]['hash'])) {
        session_regenerate_id(true);
        $_SESSION['member'] = $name;
        header('Location: /books.php', true, 303);
        exit;
    }
    http_response_code(422);
    echo "<p role=\"alert\">Wrong name or password.</p>\n";
}
?>
<form method="post" action="/login.php">
    <input type="hidden" name="_token" value="<?= token() ?>">
    <input name="name" value="<?= htmlspecialchars($name, ENT_QUOTES) ?>">
    <input type="password" name="password">
    <button>Log in</button>
</form>
