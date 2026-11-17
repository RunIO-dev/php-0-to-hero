<?php

declare(strict_types=1);

// Exercise 38, intermediate: what does a constraint allow? A solution.

function allows(string $constraint, string $version): bool
{
    $operator = $constraint[0] ?? '';
    if ($operator !== '^' && $operator !== '~') {
        return version_compare($version, $constraint, '==');
    }
    $parts = array_map(fn(string $part): int => (int) $part, explode('.', substr($constraint, 1)));

    return version_compare($version, implode('.', $parts + [0, 0, 0]), '>=')
        && version_compare($version, firstNotAllowed($operator, $parts), '<');
}

/**
 * The first version above the range: one part goes up by 1, and the parts after it become 0.
 *
 * @param list<int> $parts the numbers written in the constraint
 */
function firstNotAllowed(string $operator, array $parts): string
{
    if ($operator === '~') {
        $raise = max(0, count($parts) - 2);
    } else {
        $nonZero = array_keys(array_filter($parts, fn(int $part): bool => $part !== 0));
        $raise = $nonZero[0] ?? count($parts) - 1;
    }
    $next = [0, 0, 0];
    foreach ($parts as $i => $part) {
        if ($i < $raise) {
            $next[$i] = $part;
        }
    }
    $next[$raise] = $parts[$raise] + 1;

    return implode('.', $next);
}
