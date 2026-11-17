<?php

declare(strict_types=1);

// The same page, safe: the visitor's text is escaped before it goes into the HTML.

$search = $_GET['q'] ?? '';
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Search</title></head>
<body>
    <p>You searched for: <?= htmlspecialchars($search) ?></p>
</body>
</html>
