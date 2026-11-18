<?php

declare(strict_types=1);

// Before Shelf's tests (chapter 77): Composer's autoloader, the settings from .env and .env.test (as
// bin/console and public/index.php read them; phpunit.xml.dist sets APP_ENV to test), and the test
// database, brought up to date by the migrations once, before the first test. Each test then runs in a
// transaction that is rolled back (dama/doctrine-test-bundle): the tables stay, and stay empty.

use Shelf\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';

new Dotenv()->bootEnv(dirname(__DIR__) . '/.env');

// Chapter 79: a new checkout, as in CI, has no var/ yet; Symfony's test log (services.yaml) needs its folder.
new Filesystem()->mkdir(dirname(__DIR__) . '/var/log');

$kernel = new Kernel('test', false);
$console = new Application($kernel);
$console->setAutoExit(false);
$output = new BufferedOutput();
if ($console->run(new ArrayInput(['command' => 'doctrine:migrations:migrate', '--no-interaction' => true]), $output) !== 0) {
    // Chapter 79: exit with a text would end with status 0, and CI would pass without a single test.
    fwrite(STDERR, "The test database could not be migrated:\n" . $output->fetch());
    exit(1);
}
$kernel->shutdown();
