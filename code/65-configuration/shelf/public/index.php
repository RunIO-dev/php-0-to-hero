<?php

declare(strict_types=1);

// Shelf on the web (chapters 48-65): the front controller. Every request comes here; the built-in
// server sends a path without a file, such as /add, to index.php. App decides what to answer.

use Shelf\Accounts;
use Shelf\Config;
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

$config = Config::load(dirname(__DIR__)); // chapter 65: the settings, from .env and the environment
$db = openDatabase($config->database);
$members = new PdoMemberRepository($db);
$library = new Library(new PdoBookRepository($db), $members, new PdoLoanRepository($db));
$app = new App($library, new Accounts($members), new Templates(__DIR__ . '/../templates'), new PhpSession(), new Covers($config->covers));
$app->handle(Request::fromGlobals())->send();
