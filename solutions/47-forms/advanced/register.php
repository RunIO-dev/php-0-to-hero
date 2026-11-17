<?php

declare(strict_types=1);

// Exercise 47, advanced: the page of a sign-up form.

require __DIR__ . '/rules.php';

$values = values([]);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = values($_POST);
    $errors = errors($values);
    if ($errors === []) {
        header('Location: /welcome.php', true, 303);
        exit;
    }
    http_response_code(422);
}

/**
 * @param array<string, string> $values
 * @param array<string, string> $errors
 */
function field(string $name, array $values, array $errors): string
{
    $error = isset($errors[$name]) ? ' <span class="error">' . htmlspecialchars($errors[$name]) . '</span>' : '';

    return '<input name="' . $name . '" value="' . htmlspecialchars($values[$name] ?? '') . '">' . $error;
}
?>
<form method="post" action="/register.php">
    <p>Name <?= field('name', $values, $errors) ?></p>
    <p>Email <?= field('email', $values, $errors) ?></p>
    <p>Year of birth <?= field('year', $values, $errors) ?></p>
    <p><label><input type="checkbox" name="terms" value="yes"> I accept the rules</label><?= isset($errors['terms']) ? ' <span class="error">' . htmlspecialchars($errors['terms']) . '</span>' : '' ?></p>
    <button>Sign up</button>
</form>
