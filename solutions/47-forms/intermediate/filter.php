<?php

declare(strict_types=1);

// Exercise 47, intermediate: a search form that remembers.

const BOOKS = [
    ['title' => 'Kindred', 'year' => 1979],
    ['title' => 'Kitchen', 'year' => 1988],
    ['title' => 'Things Fall Apart', 'year' => 1958],
    ['title' => 'Parable of the Sower', 'year' => 1993],
];

$q = trim($_GET['q'] ?? '');
$before = trim($_GET['before'] ?? '');
$error = null;
$limit = $before === '' ? PHP_INT_MAX : filter_var($before, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2100]]);
if ($limit === false) {
    $error = 'The year must be a number from 1 to 2100.';
    http_response_code(400);
    $found = [];
} else {
    $found = array_filter(
        BOOKS,
        fn(array $book): bool => ($q === '' || stripos($book['title'], $q) !== false) && $book['year'] < $limit,
    );
}
?>
<form method="get" action="/filter.php">
<?php if ($error !== null): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
    <input name="q" value="<?= htmlspecialchars($q) ?>">
    <input name="before" value="<?= htmlspecialchars($before) ?>">
    <button>Search</button>
</form>
<ul>
<?php foreach ($found as $book): ?>
    <li><?= htmlspecialchars($book['title']) ?> (<?= $book['year'] ?>)</li>
<?php endforeach; ?>
</ul>
