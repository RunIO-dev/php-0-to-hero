<?php

declare(strict_types=1);

// Where join.php sends the browser after a success.

$name = $_GET['name'] ?? '';
?>
<p>Welcome, <?= htmlspecialchars($name) ?>!</p>
