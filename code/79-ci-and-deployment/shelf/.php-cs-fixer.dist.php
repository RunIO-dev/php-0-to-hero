<?php

declare(strict_types=1);

// Shelf's code style (chapter 79): PER Coding Style, as the course checks it, now in Shelf itself so
// that its own CI can check it.
$finder = new PhpCsFixer\Finder()
    ->in(['bin', 'config', 'migrations', 'public', 'src', 'tests'])
    ->notPath('reference.php'); // made by Symfony in debug mode (chapter 72)

return new PhpCsFixer\Config()
    ->setRules(['@PER-CS' => true])
    // PHP-CS-Fixer 3.95 knows PHP syntax up to 8.5; remove this once it supports 8.6.
    ->setUnsupportedPhpVersionAllowed(true)
    ->setCacheFile('var/.php-cs-fixer.cache')
    ->setFinder($finder);
