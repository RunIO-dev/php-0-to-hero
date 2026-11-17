<?php

declare(strict_types=1);

// Exercise 50, beginner: remember a language in a cookie.
//
// - GET /language.php?lang=de (or en, or ar): save it in a cookie named lang for a year, with the
//   path /, secure, httponly and SameSite Lax, and answer "Saved: de".
// - Otherwise answer "Language: xx", from the cookie. A cookie that isn't en, de or ar (anyone
//   can send any cookie) counts as en, as no cookie does.

header('Content-Type: text/plain; charset=UTF-8');

echo "Language: en\n";
// Check your work: composer exercise 50 beginner
