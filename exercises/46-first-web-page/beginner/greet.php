<?php

declare(strict_types=1);

// Exercise 46, beginner: greet safely.
//
// /greet.php?name=Amira says "Hello, Amira!" in the page. Without a name, "Hello, reader!".
// The name comes from the address, so anyone can put HTML in it: escape it.

$name = $_GET['name'] ?? '';
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Hello</title></head>
<body>
    <h1>Hello, <?= $name ?>!</h1>
</body>
</html>
<?php // Check your work: composer exercise 46 beginner
