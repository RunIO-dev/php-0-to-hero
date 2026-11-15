<?php

declare(strict_types=1);

// Code style: PER Coding Style, the latest release (docs/standards.md §4).
$finder = (new PhpCsFixer\Finder())
    ->in(['code', 'exercises', 'solutions', 'shelf', 'tools', 'tests'])
    ->append(['tools/verify', 'tools/exercise', 'tools/book'])
    ->exclude('vendor')
    ->notName('check.php'); // old-style on purpose, so it runs on old PHP and can say: upgrade

return (new PhpCsFixer\Config())
    ->setRules(['@PER-CS' => true])
    // PHP-CS-Fixer 3.95 knows PHP syntax up to 8.5; remove this once it supports 8.6.
    ->setUnsupportedPhpVersionAllowed(true)
    ->setFinder($finder);
