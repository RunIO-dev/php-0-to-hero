<?php

declare(strict_types=1);

// Exercise 48, advanced: values in the path.
//
// matchRoute($pattern, $path) compares a route pattern such as '/books/{id}' with a path such as
// '/books/42'. It returns the values of the {names}, ['id' => '42'], or null if the path doesn't fit.
// - Split both at '/'. They fit when they have the same number of parts, and every part is equal,
//   or is a {name} in the pattern and not empty in the path.
// - The values are decoded: '/books/a%20b' gives 'a b' (rawurldecode).
// - Every other character of the pattern means itself: '/a.b' fits '/a.b', not '/axb'.

/** @return array<string, string>|null */
function matchRoute(string $pattern, string $path): ?array
{
    return null;
}
// Check your work: composer exercise 48 advanced
