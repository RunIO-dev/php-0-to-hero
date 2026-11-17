<?php

declare(strict_types=1);

// The same form with a CSRF token: a random secret in the session, and in a hidden field of
// the form. Another site can make the browser send the form, but can't read the token.

session_start(['cookie_httponly' => true, 'cookie_secure' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);
$_SESSION['member'] ??= 'Amira';
$token = $_SESSION['csrf'] ??= bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: text/plain; charset=UTF-8');
    $sent = $_POST['_token'] ?? '';
    if (!is_string($sent) || !is_string($token) || !hash_equals($token, $sent)) {
        http_response_code(403);
        echo "This form has expired. Please reload the page and try again.\n";
        exit;
    }
    echo 'Deleted all loans of ', htmlspecialchars((string) $_SESSION['member']), ".\n";
    exit;
}
?>
<form method="post" action="/delete.php">
    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $token) ?>">
    <button>Delete all my loans</button>
</form>
