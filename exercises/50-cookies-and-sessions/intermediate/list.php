<?php

declare(strict_types=1);

// Exercise 50, intermediate: a reading list in the session.
//
// - Start the session with httponly, secure and SameSite Lax cookies, and strict mode.
// - POST /list.php with title=...: add the title (trimmed) to $_SESSION['list'], unless it is
//   empty or already there. A list has at most 3 titles; a fourth answers 422 and "Your list is full."
//   After a successful POST, redirect to /list.php with 303.
// - GET: "3 on your list" (the number), then one title per line.

header('Content-Type: text/plain; charset=UTF-8');

echo "0 on your list\n";
// Check your work: composer exercise 50 intermediate
