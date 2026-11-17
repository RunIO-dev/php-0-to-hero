<?php

declare(strict_types=1);

// A form with method="post", checked on the server. A mistake: the form again, with the
// values and the reasons. Success: a redirect, so that reloading the page sends nothing twice.

$name = '';
$year = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $year = trim($_POST['year'] ?? '');
    if ($name === '' || mb_strlen($name) > 50) {
        $errors[] = 'A name of 1 to 50 characters, please.';
    }
    if (filter_var($year, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900, 'max_range' => 2026]]) === false) {
        $errors[] = 'A year of birth from 1900 to 2026, please.';
    }
    if ($errors === []) {
        header('Location: /welcome.php?name=' . urlencode($name), true, 303);
        exit;
    }
    http_response_code(422);
}
?>
<form method="post" action="/join.php">
<?php foreach ($errors as $error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endforeach; ?>
    <input name="name" value="<?= htmlspecialchars($name) ?>">
    <input name="year" value="<?= htmlspecialchars($year) ?>">
    <button>Join</button>
</form>
