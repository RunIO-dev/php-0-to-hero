<?php

declare(strict_types=1);

// Exercise 46, intermediate: a table of books.
//
// Show BOOKS as an HTML table: one <tr> per book, with a <td> for the title and one
// for the year. Put the full title into a title attribute of the row too, so the
// browser shows it on hover: <tr title="Tom &amp; Jerry">. Escape everything.
// Use <?= and foreach (...): endforeach; for the rows.

const BOOKS = [
    ['title' => 'Tom & Jerry', 'year' => 1940],
    ['title' => 'The "Quiet" American', 'year' => 1955],
    ['title' => "Butler's <i>Kindred</i>", 'year' => 1979],
];
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Books</title></head>
<body>
    <table>
        <!-- your rows here -->
    </table>
</body>
</html>
<?php // Check your work: composer exercise 46 intermediate
