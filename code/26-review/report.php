<?php

declare(strict_types=1);

// Loads the functions from format.php, once, from this file's folder.
require_once __DIR__ . '/format.php';

echo plural(3, 'book'), ' on loan, ', plural(1, 'book'), " late\n";
echo 'Fees: ', money(150), "\n";
