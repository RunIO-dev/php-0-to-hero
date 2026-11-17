<?php

declare(strict_types=1);

// Exercise 48, beginner: a front controller for a small site.
// Start it with: php -S localhost:8000 site.php
//
// Answer in plain text:
//   GET /         → "Home"
//   GET /about    → "About us"
//   GET /opening  → "Monday to Friday, 9 to 18"
// Every other path: status 404 and "Not found". A query string (/about?x=1) doesn't change the page.

header('Content-Type: text/plain; charset=UTF-8');

echo "Home\n";
// Check your work: composer exercise 48 beginner
