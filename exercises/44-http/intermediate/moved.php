<?php

declare(strict_types=1);

// Exercise 44, intermediate: pages that moved.
//
// The old site had addresses such as /moved.php?page=list. Send each old page in
// MOVED to its new address, with 301 Moved Permanently and a Location header.
// Any other page, or none: 404 Not Found, with the plain text "Not found" and a new line.

const MOVED = ['list' => '/books.php', 'about' => '/about.php', 'join' => '/members/join.php'];

// your code here

// Check your work: composer exercise 44 intermediate
