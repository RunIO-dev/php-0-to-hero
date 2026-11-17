<?php

declare(strict_types=1);

// Exercise 45, intermediate: a health check.
//
// Docker, and the people who run a site, ask a page such as this one whether the site can
// work. Answer in JSON, with Content-Type: application/json:
// - 200 and {"status":"ok","books":1} when the books file can be read and holds a list
//   (books: how many are in it);
// - 503 Service Unavailable and {"status":"down","reason":"..."} otherwise, with a short reason.
// The file is the environment variable BOOKS_FILE, a path inside this folder, or books.json.

// your code here

// Check your work: composer exercise 45 intermediate
