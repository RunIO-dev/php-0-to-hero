<?php

declare(strict_types=1);

// Shelf on the web (chapters 48-66): the front controller. Every request comes here; the built-in
// server sends a path without a file, such as /add, to index.php. App decides what to answer.

use Shelf\Accounts;
use Shelf\Config;
use Shelf\FileLogger;
use Shelf\Library;
use Shelf\PdoBookRepository;
use Shelf\PdoLoanRepository;
use Shelf\PdoMemberRepository;
use Shelf\Web\App;
use Shelf\Web\Covers;
use Shelf\Web\PhpSession;
use Shelf\Web\Request;
use Shelf\Web\Response;
use Shelf\Web\Templates;

use function Shelf\openDatabase;

require __DIR__ . '/../vendor/autoload.php';

$config = Config::load(dirname(__DIR__)); // chapter 65: the settings, from .env and the environment
$logger = new FileLogger($config->log); // chapter 66

// PHP's own warnings: on the screen on a developer's computer, never in production; always in the log.
ini_set('display_errors', $config->isProduction() ? '0' : '1');
ini_set('log_errors', '1');
ini_set('error_log', $config->log);

try {
    $db = openDatabase($config->database);
    $members = new PdoMemberRepository($db);
    $library = new Library(new PdoBookRepository($db), $members, new PdoLoanRepository($db));
    $templates = new Templates(__DIR__ . '/../templates');
    $app = new App($library, new Accounts($members), $templates, new PhpSession(), new Covers($config->covers), $logger);
    $response = $app->handle(Request::fromGlobals());
} catch (Throwable $e) {
    $response = Response::error($e, $logger, showDetails: !$config->isProduction());
}
$response->send();
