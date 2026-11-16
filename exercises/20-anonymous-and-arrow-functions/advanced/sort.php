<?php

declare(strict_types=1);

// Exercise 20, advanced: sort by author, then by year.
//
// Write a function byAuthorThenYear($books) that returns the books sorted by
// author (in any case, A to Z) and, for the same author, by year (oldest
// first). Use usort with an arrow function.
// Tip: [$a['author'], ...] <=> [$b['author'], ...] compares lists item by
// item, but it compares text by character codes. strcasecmp() ignores case.
//
// Check your work: composer exercise 20 advanced
