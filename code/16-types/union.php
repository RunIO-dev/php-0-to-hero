<?php

declare(strict_types=1);

// int|float: either type is fine.
// (An int is also accepted where a float is expected.)
function euros(int|float $cents): string
{
    return number_format($cents / 100, 2) . ' EUR';
}

echo euros(1299), "\n";
echo euros(49.5), "\n";
