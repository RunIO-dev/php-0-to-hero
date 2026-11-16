<?php

declare(strict_types=1);

function lend(int $copies): int
{
    if ($copies === 0) {
        throw new RuntimeException('All copies are on loan');
    }

    return $copies - 1;
}

$copies = 0;
try {
    $copies = lend($copies);
} catch (RuntimeException $e) {
    // nothing here
}

echo "Lent. Copies left: $copies\n";
