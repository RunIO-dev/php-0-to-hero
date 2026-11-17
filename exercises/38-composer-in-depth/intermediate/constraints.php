<?php

declare(strict_types=1);

// Exercise 38, intermediate: what does a constraint allow?
//
// Write allows(), which says whether a Composer constraint allows a version.
// Versions have three parts, such as 1.2.3. Constraints come in three kinds:
// - "1.2.3": exactly this version;
// - "^1.2": from 1.2.0 up to, not including, the next major version, 2.0.0.
//   Before 1.0, the first part that is not 0 counts: ^0.3 is up to 0.4.0, and
//   ^0.0.3 is up to 0.0.4;
// - "~1.2.3": from 1.2.3 up to, not including, 1.3.0; "~1.2" is up to 2.0.0.
// Use version_compare to compare versions.

function allows(string $constraint, string $version): bool
{
    return false; // your code here
}

// Check your work: composer exercise 38 intermediate
