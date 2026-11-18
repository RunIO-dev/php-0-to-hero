<?php

declare(strict_types=1);

// Settings in production (chapter 78): the server sets real environment variables, and they win over
// .env. Here, $_SERVER plays the server, as Docker's "environment:" sets it for a container.

use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$_SERVER['APP_ENV'] = 'prod';
$_SERVER['DATABASE_URL'] = 'sqlite:////app/data/shelf.sqlite';

new Dotenv()->loadEnv(__DIR__ . '/.env'); // .env, then .env.local, .env.prod, .env.prod.local if they exist

// A script for the terminal, not a page: it writes to STDOUT, so nothing here needs HTML escaping.
foreach (['APP_ENV', 'DATABASE_URL', 'SHELF_LOG'] as $name) {
    fwrite(STDOUT, "$name = {$_SERVER[$name]}\n");
}
