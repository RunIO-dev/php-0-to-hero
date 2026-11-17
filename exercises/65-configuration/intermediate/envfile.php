<?php

declare(strict_types=1);

// Exercise 65, intermediate: read a .env file.
//
// readEnvFile($file) returns the settings in the file as [NAME => value]:
// - empty lines, and lines that start with # (after spaces), are skipped;
// - every other line is NAME=value, where NAME is capital letters, digits and _, starting with a letter;
// - spaces around the line are dropped, and one pair of double quotes around the value;
// - a value may contain = (PASSWORD=a=b gives "a=b");
// - a later line with the same NAME wins;
// - any other line: an UnexpectedValueException with the file's name and the line's number,
//   such as "library.env line 3: expected NAME=value".

/** @return array<string, string> */
function readEnvFile(string $file): array
{
    return [];
}
// Check your work: composer exercise 65 intermediate
