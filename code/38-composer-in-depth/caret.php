<?php

declare(strict_types=1);

// What Composer's ^ allows from version 1.0 on, written out with version_compare.

/** ^1.2 allows 1.2.0 and every newer version below the next major version, 2.0.0. */
function caretAllows(string $constraint, string $version): bool
{
    $lowest = ltrim($constraint, '^');
    $nextMajor = ((int) $lowest + 1) . '.0.0';

    return version_compare($version, $lowest, '>=') && version_compare($version, $nextMajor, '<');
}

foreach (['1.1.9', '1.2.0', '1.2.5', '1.9.0', '2.0.0'] as $version) {
    echo "^1.2 and $version: ", caretAllows('^1.2', $version) ? 'allowed' : 'not allowed', "\n";
}
