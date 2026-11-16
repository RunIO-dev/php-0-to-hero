<?php

declare(strict_types=1);

// ^ start, \d a digit, {5} five times, $ end: exactly five digits.
$pattern = '/^\d{5}$/';

foreach (['10115', '1011', '101155', '1O115'] as $postcode) {
    echo str_pad($postcode, 8), preg_match($pattern, $postcode) === 1 ? 'yes' : 'no', "\n";
}
