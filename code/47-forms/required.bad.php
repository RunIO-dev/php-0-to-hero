<?php

declare(strict_types=1);

// Checked only in the browser: "required" and "maxlength" stop a person, not a request.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    echo 'Saved a member named "', htmlspecialchars($name), '" (', strlen($name), " characters)\n";
    exit;
}
?>
<form method="post" action="/required.bad.php">
    <input name="name" required maxlength="50">
    <button>Join</button>
</form>
