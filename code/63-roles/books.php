<?php

declare(strict_types=1);

// Everyone may see the books. Only staff see the button to remove one, but the button is not the check.

require_once __DIR__ . '/account.php';

header('Content-Type: text/plain; charset=UTF-8');

echo 'Visitor: ', member() ?? 'not logged in', ', role: ', role() ?? 'none', "\n";
foreach (['Kindred', 'Sula'] as $title) {
    echo "- $title", role() === 'staff' ? '   [Remove]' : '', "\n";
}
