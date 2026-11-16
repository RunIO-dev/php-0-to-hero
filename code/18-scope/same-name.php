<?php

declare(strict_types=1);

function countdown(int $count): void
{
    // This $count is the function's own copy of the argument.
    while ($count > 0) {
        $count--;
    }
    echo "inside: $count\n";
}

$count = 3;
countdown($count);
echo "outside: $count\n";
