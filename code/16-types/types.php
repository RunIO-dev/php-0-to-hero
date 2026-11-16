<?php

declare(strict_types=1);

function pricePerPage(int $priceCents, int $pages): float
{
    return $priceCents / $pages;
}

function isLong(int $pages): bool
{
    return $pages > 400;
}

// void: the function returns nothing. It is called for what it does.
function printRule(): void
{
    echo "----------\n";
}

printRule();
echo round(pricePerPage(1299, 320), 2), " cents per page\n";
var_dump(isLong(320));
printRule();
