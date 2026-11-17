<?php

declare(strict_types=1);

// A page that knows who is logged in, from the session, and offers to log out.

require_once __DIR__ . '/account.php';

$member = member();
if ($member === null) {
    exit("<p>Not logged in. <a href=\"/login.php\">Log in</a></p>\n");
}
?>
<p>Logged in as <?= htmlspecialchars($member, ENT_QUOTES) ?></p>
<form method="post" action="/logout.php">
    <input type="hidden" name="_token" value="<?= token() ?>">
    <button>Log out</button>
</form>
