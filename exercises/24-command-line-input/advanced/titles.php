<?php

declare(strict_types=1);

// Exercise 24, advanced: a clean list of titles.
//
// Read titles from the input, one per line, until it ends:
//   php titles.php < wishes.txt      (or type them, and end with Ctrl+D)
// Ignore spaces around a title and empty lines, and keep each title only
// once, in any case ("kindred" is the same as "Kindred": keep the first).
// Print them sorted A to Z in any case, numbered, then the count:
//   1. Beloved
//   2. Kindred
//   2 titles
// (and "1 title" when there is only one).
// With no titles at all, print: No titles
//
// Check your work: composer exercise 24 advanced
