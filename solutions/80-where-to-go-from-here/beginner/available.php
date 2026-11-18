<?php

declare(strict_types=1);

// Will the code run on the server (chapter 80)? Each function's page in the manual says since which PHP
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
    $server = release($version);
    $missing = [];
    foreach ($since as $function => $added) {
        if (version_compare(release($added), $server, '>')) {
            $missing[] = $function;
        }
    }
    sort($missing);

    return $missing;
}

/** "8.3" -> "8.3.0", "8.6.0RC7" -> "8.6.0": three numbers that version_compare() compares as they are meant. */
function release(string $version): string
{
    if (preg_match('/^(\d+)\.(\d+)(?:\.(\d+))?(?:-?(?:alpha|beta|rc)\d*)?$/iD', $version, $m) !== 1) {
        throw new InvalidArgumentException("Not a PHP version: \"$version\"");
    }

    return "$m[1].$m[2]." . ($m[3] ?? '0');
}
