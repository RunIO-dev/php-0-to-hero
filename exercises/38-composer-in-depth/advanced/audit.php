<?php

declare(strict_types=1);

// Exercise 38, advanced: a small composer audit.
//
// Write affected(): it reads a lock file (such as composer.lock in this folder)
// and returns one line for every installed package that an advisory affects,
// in "packages" and in "packages-dev", sorted by package name:
//   example/http-client 7.4.4: Cookies sent to the wrong host (fixed in 7.4.5)
// An advisory affects every version below its "fixed" version, and only that package.
// Compare versions with version_compare.

/**
 * @param list<array{package: string, fixed: string, title: string}> $advisories
 * @return list<string>
 */
function affected(string $lockFile, array $advisories): array
{
    return []; // your code here
}

// Check your work: composer exercise 38 advanced
