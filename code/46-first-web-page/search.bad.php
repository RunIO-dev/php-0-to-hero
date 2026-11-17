<?php

declare(strict_types=1);

// A search page that puts the visitor's text into the HTML as it is.

$search = $_GET['q'] ?? '';
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Search</title></head>
<body>
    <p>You searched for: <?= $search ?></p>
</body>
</html>
