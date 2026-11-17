<?php

declare(strict_types=1);

// A form with method="get": the browser puts the fields into the address, ?q=...

const TITLES = ['Kindred', 'Kitchen', 'Sula', 'Things Fall Apart'];

$q = trim($_GET['q'] ?? '');
$found = $q === '' ? [] : array_filter(TITLES, fn(string $title): bool => stripos($title, $q) !== false);
?>
<form method="get" action="/search.php">
    <input name="q" value="<?= htmlspecialchars($q) ?>">
    <button>Search</button>
</form>
<?php foreach ($found as $title): ?>
<p><?= htmlspecialchars($title) ?></p>
<?php endforeach; ?>
