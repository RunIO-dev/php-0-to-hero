<?php

declare(strict_types=1);

// A version number has three parts, MAJOR.MINOR.PATCH, and each part makes a promise.

/** Which part changed between two versions, and what that promises. */
function change(string $from, string $to): string
{
    [$old, $new] = [explode('.', $from), explode('.', $to)];

    return match (true) {
        $old[0] !== $new[0] => 'major: may break code that uses it',
        $old[1] !== $new[1] => 'minor: new features, nothing breaks',
        default => 'patch: bug fixes only',
    };
}

foreach ([['3.0.1', '3.0.2'], ['3.0.2', '3.1.0'], ['3.1.0', '4.0.0']] as [$from, $to]) {
    echo "$from -> $to  ", change($from, $to), "\n";
}
