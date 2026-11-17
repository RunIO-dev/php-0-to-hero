<?php

declare(strict_types=1);

// Shelf on the web (chapters 48-56): the front controller. Every request comes here; the built-in
// server sends a path without a file, such as /add, to index.php. App decides what to answer.

use Shelf\Library;
use Shelf\PdoBookRepository;
use Shelf\PdoLoanRepository;
use Shelf\PdoMemberRepository;
use Shelf\Web\App;
use Shelf\Web\Covers;
use Shelf\Web\PhpSession;
use Shelf\Web\Request;
use Shelf\Web\Templates;

use function Shelf\openDatabase;

require __DIR__ . '/../vendor/autoload.php';

$db = openDatabase(__DIR__ . '/../var/shelf.sqlite');
$library = new Library(new PdoBookRepository($db), new PdoMemberRepository($db), new PdoLoanRepository($db));
$app = new App($library, new Templates(__DIR__ . '/../templates'), new PhpSession(), new Covers(__DIR__ . '/../storage/covers'));
$app->handle(Request::fromGlobals())->send();
