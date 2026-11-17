<?php

declare(strict_types=1);

// Shelf on the web (chapters 48-50): the front controller. Every request comes here; the built-in
// server sends a path without a file, such as /add, to index.php. App decides what to answer.

use Shelf\JsonBookRepository;
use Shelf\Library;
use Shelf\Web\App;
use Shelf\Web\PhpSession;
use Shelf\Web\Request;
use Shelf\Web\Templates;

use function Shelf\loadMembers;

require __DIR__ . '/../vendor/autoload.php';

$library = new Library(new JsonBookRepository(__DIR__ . '/../books.json'), loadMembers(__DIR__ . '/../members.json'));
(new App($library, new Templates(__DIR__ . '/../templates'), new PhpSession()))->handle(Request::fromGlobals())->send();
