<?php

declare(strict_types=1);

// Exercise 46, beginner: greet safely. A solution.

$name = $_GET['name'] ?? '';
$name = is_string($name) && trim($name) !== '' ? trim($name) : 'reader';
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Hello</title></head>
<body>
    <h1>Hello, <?= htmlspecialchars($name) ?>!</h1>
</body>
</html>
