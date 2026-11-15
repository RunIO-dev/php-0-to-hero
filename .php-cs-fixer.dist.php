<?php

declare(strict_types=1);

// Code style: PER Coding Style, the latest release (docs/standards.md §4).
$finder = (new PhpCsFixer\Finder())
    // only folders that exist yet: git keeps no empty folders
    ->in(array_values(array_filter(['code', 'exercises', 'solutions', 'shelf', 'tools', 'tests'], 'is_dir')))
    ->append(['tools/verify', 'tools/exercise', 'tools/book', 'tools/out'])
    ->exclude('vendor')
    ->notName('check.php') // old-style on purpose, so it runs on old PHP and can say: upgrade
    ->notName('*.error.php'); // shows an error on purpose

return (new PhpCsFixer\Config())
    ->setRules(['@PER-CS' => true])
    // PHP-CS-Fixer 3.95 knows PHP syntax up to 8.5; remove this once it supports 8.6.
    ->setUnsupportedPhpVersionAllowed(true)
    ->setFinder($finder);
