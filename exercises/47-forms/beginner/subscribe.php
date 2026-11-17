<?php

declare(strict_types=1);

// Exercise 47, beginner: a newsletter form.
//
// The form below posts one field, email. When it arrives with POST:
// - if it is a valid email address (filter_var with FILTER_VALIDATE_EMAIL), redirect to
//   /thanks.php with the status 303;
// - otherwise, answer 422, and show the form again with <p class="error">An email address,
//   please.</p> above it and what was typed in the field, escaped.

$email = '';
$error = null;
?>
<form method="post" action="/subscribe.php">
<?php if ($error !== null): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
    <input name="email" value="">
    <button>Subscribe</button>
</form>
<?php // Check your work: composer exercise 47 beginner
