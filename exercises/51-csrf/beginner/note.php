<?php

declare(strict_types=1);

// Exercise 51, beginner: protect a form with a CSRF token.
//
// - Keep a token in $_SESSION['csrf']: 32 random bytes as hexadecimal (random_bytes, bin2hex),
//   made once per session.
// - Put it into the form as <input type="hidden" name="_token" value="...">.
// - A POST whose _token isn't exactly the session's token: status 403 and "Expired.". Compare
//   with hash_equals. A POST with the right token: "Saved.".

session_start(['cookie_httponly' => true, 'cookie_secure' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Saved.\n";
    exit;
}
?>
<form method="post" action="/note.php">
    <textarea name="note"></textarea>
    <button>Save</button>
</form>
<?php // Check your work: composer exercise 51 beginner
