<?php

declare(strict_types=1);

// Exercise 47, beginner: a newsletter form.

$email = '';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
        header('Location: /thanks.php', true, 303);
        exit;
    }
    $error = 'An email address, please.';
    http_response_code(422);
}
?>
<form method="post" action="/subscribe.php">
<?php if ($error !== null): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
    <input name="email" value="<?= htmlspecialchars($email) ?>">
    <button>Subscribe</button>
</form>
