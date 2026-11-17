<?php

declare(strict_types=1);

// Shelf on the web (chapter 48): the front controller. Every request comes here; the built-in
// server sends a path without a file, such as /add, to index.php. App decides what to answer.

use Shelf\JsonBookRepository;
use Shelf\Library;
use Shelf\Web\App;
use Shelf\Web\Request;

use function Shelf\loadMembers;

require __DIR__ . '/../vendor/autoload.php';

$library = new Library(new JsonBookRepository(__DIR__ . '/../books.json'), loadMembers(__DIR__ . '/../members.json'));
(new App($library))->handle(Request::fromGlobals())->send();
