<?php

declare(strict_types=1);

// Without ^ and $, the pattern only has to appear somewhere in the text.
foreach (['10115', 'abc10115xyz', '1011522'] as $input) {
    echo str_pad($input, 13), preg_match('/\d{5}/', $input), ' ', preg_match('/^\d{5}$/', $input), "\n";
}
