<?php

declare(strict_types=1);

function lend(int $copies): int
{
    if ($copies === 0) {
        throw new RuntimeException('All copies are on loan');
    }

    return $copies - 1;
}

foreach ([1, 0] as $copies) {
    try {
        $left = lend($copies);
        echo "Lent. Copies left: $left\n";
    } catch (RuntimeException $e) {
        echo $e->getMessage(), "\n";
    } finally {
        // runs in both cases
        echo "Next member, please.\n";
    }
}
