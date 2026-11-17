<?php

declare(strict_types=1);

// Exercise 47, intermediate: a search form that remembers.
//
// The form uses GET with two fields: q (a part of the title) and before (a year).
// - Show every book whose title contains q (any case) and whose year is below before.
//   An empty field doesn't filter.
// - A before that isn't a whole number from 1 to 2100: answer 400 with
//   <p class="error">The year must be a number from 1 to 2100.</p>, and no books.
// - The fields keep what was typed, escaped, so the visitor can change the search.
// - Each book is <li>Title (year)</li>, escaped.

const BOOKS = [
    ['title' => 'Kindred', 'year' => 1979],
    ['title' => 'Kitchen', 'year' => 1988],
    ['title' => 'Things Fall Apart', 'year' => 1958],
    ['title' => 'Parable of the Sower', 'year' => 1993],
];

$found = BOOKS;
?>
<form method="get" action="/filter.php">
    <input name="q" value="">
    <input name="before" value="">
    <button>Search</button>
</form>
<ul>
<?php foreach ($found as $book): ?>
    <li><?= htmlspecialchars($book['title']) ?> (<?= $book['year'] ?>)</li>
<?php endforeach; ?>
</ul>
<?php // Check your work: composer exercise 47 intermediate
