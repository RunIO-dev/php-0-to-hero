<?php

declare(strict_types=1);

// Will the code run on the server (chapter 89)? Each function's page in the manual says since which PHP
// version it exists: "(PHP 8 >= 8.4.0)". missingOn() gets the server's PHP version and, for each function
// the code uses, that version, and returns the functions the server doesn't have yet, sorted by name.
// - A version is "8.3" or "8.3.12": "8.3" is "8.3.0", so json_validate (8.3.0) is there.
// - A release candidate counts as its release: "8.6.0RC7" has every function "since 8.6.0".
//   (alpha, beta and RC, with or without a "-" before them, in any case.)
// - Any other version, the server's or a function's: InvalidArgumentException.
//
//   missingOn("8.3.12", ["str_contains" => "8.0.0", "array_find" => "8.4.0"])  ->  ["array_find"]

/**
 * @param array<string, string> $since function name => the version that added it
 * @return list<string>
 */
function missingOn(string $version, array $since): array
{
    return []; // TODO
}
