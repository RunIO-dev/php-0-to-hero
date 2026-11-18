<?php

declare(strict_types=1);

// The autoloader in production (chapter 78): composer dump-autoload --classmap-authoritative lists
// every class with its file in advance, and never looks for a file that isn't on the list. Here the
// same thing, with Composer's ClassLoader, step by step.

use Composer\Autoload\ClassLoader;

require __DIR__ . '/vendor/autoload.php';

$loader = new ClassLoader();
$loader->addPsr4('Library\\', __DIR__ . '/src/');   // as in composer.json: a folder to look in
$loader->addClassMap(['Library\\Book' => __DIR__ . '/src/Book.php']);
$loader->setClassMapAuthoritative(true);              // the list is complete

echo 'Library\Book: ', $loader->findFile('Library\Book') === false ? 'not found' : 'from the list', "\n";
// Member.php is in src/, but was added after the list was made: no file is looked for.
echo 'Library\Member: ', $loader->findFile('Library\Member') === false ? 'not found' : 'found', "\n";

$loader->setClassMapAuthoritative(false);             // as in development
echo 'Library\Member, looked for in src/: ', $loader->findFile('Library\Member') === false ? 'not found' : 'found', "\n";
