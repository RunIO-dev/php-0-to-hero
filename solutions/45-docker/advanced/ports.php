<?php

declare(strict_types=1);

// Exercise 45, advanced: who can connect? A solution.

function exposure(string $mapping): string
{
    if (preg_match('/^(?:(\[::1\]|[0-9.]+):)?(\d{1,5}):(\d{1,5})$/D', $mapping, $m) !== 1 || (int) $m[2] > 65535 || (int) $m[3] > 65535) {
        throw new InvalidArgumentException("\"$mapping\" is not a port mapping");
    }
    $local = in_array($m[1], ['127.0.0.1', '[::1]'], true);

    return ($local ? 'only this computer' : 'every network') . ", on port {$m[2]}";
}
