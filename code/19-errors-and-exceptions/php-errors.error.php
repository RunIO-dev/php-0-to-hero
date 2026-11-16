<?php

declare(strict_types=1);

// PHP's own errors are thrown too. They mean a bug, not a situation to handle.
try {
    echo intdiv(10, 0), "\n";
} catch (DivisionByZeroError $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}

// A warning is not thrown: the program goes on, with a wrong value.
$titles = ['Kindred'];
echo "Second title: \"{$titles[1]}\"\n";
