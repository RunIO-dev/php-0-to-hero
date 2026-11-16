<?php

declare(strict_types=1);

// \w is a letter, digit or _. Without the u flag, PHP sees bytes, not letters.
foreach (['Jonas', 'Jürgen', 'Ana Lima'] as $name) {
    echo mb_str_pad($name, 10), preg_match('/^\w+$/', $name), ' ', preg_match('/^\w+$/u', $name), "\n";
}
