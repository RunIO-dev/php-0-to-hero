<?php

declare(strict_types=1);

// (int) never refuses: whatever it can't read becomes 0, or the number at the start.
foreach (['7', 'abc', '7 days', '', '1e3'] as $input) {
    echo str_pad("\"$input\"", 9), (int) $input, "\n";
}
