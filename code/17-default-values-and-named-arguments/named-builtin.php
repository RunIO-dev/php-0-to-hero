<?php

declare(strict_types=1);

// Built-in functions have parameter names too. This is a German number format.
echo number_format(
    1234.5,
    decimals: 2,
    decimal_separator: ',',
    thousands_separator: '.',
), " EUR\n";
