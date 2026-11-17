<?php

declare(strict_types=1);

// Exercise 48, advanced: values in the path.

/** @return array<string, string>|null */
function matchRoute(string $pattern, string $path): ?array
{
    $want = explode('/', $pattern);
    $got = explode('/', $path);
    if (count($want) !== count($got)) {
        return null;
    }
    $values = [];
    foreach ($want as $i => $part) {
        if (preg_match('/^\{(\w+)\}$/', $part, $m) === 1) {
            if ($got[$i] === '') {
                return null;
            }
            $values[$m[1]] = rawurldecode($got[$i]);
        } elseif ($part !== $got[$i]) {
            return null;
        }
    }

    return $values;
}
