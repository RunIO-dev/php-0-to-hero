<?php

declare(strict_types=1);

// $argv: the words after "php", as a list. $argv[0] is the script's own name.
echo "$argc words\n";
foreach ($argv as $position => $word) {
    echo "$position: $word\n";
}
