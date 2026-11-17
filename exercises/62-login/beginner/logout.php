<?php

declare(strict_types=1);

// Exercise 62, beginner: log out.
//
// login.php and home.php work; this page logs the member out again.
// - It takes only POST. Any other method: status 405, with the header Allow: POST.
// - A POST must carry the session's token: call checkToken() from account.php.
// - Then forget the member, give the session a new ID, and redirect to /home.php with 303.

require_once __DIR__ . '/account.php';

header('Location: /home.php', true, 303);
// Check your work: composer exercise 62 beginner
