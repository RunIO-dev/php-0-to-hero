<?php

declare(strict_types=1);

// Exercise 51, beginner: protect a form with a CSRF token.

session_start(['cookie_httponly' => true, 'cookie_secure' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);

$token = $_SESSION['csrf'] ?? null;
if (!is_string($token)) {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf'] = $token;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = $_POST['_token'] ?? '';
    if (!is_string($sent) || !hash_equals($token, $sent)) {
        http_response_code(403);
        echo "Expired.\n";
        exit;
    }
    echo "Saved.\n";
    exit;
}
?>
<form method="post" action="/note.php">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($token) ?>">
    <textarea name="note"></textarea>
    <button>Save</button>
</form>
