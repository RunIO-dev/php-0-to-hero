<?php

declare(strict_types=1);

// Exercise 22, advanced: members from a CSV file.
//
// Write a function loadMembers(string $path): array that reads a CSV file
// whose first line is the header "name,email", and returns the members as a
// list of maps:
//   [['name' => 'Amira', 'email' => 'amira@example.org'], ...]
// A name may contain a comma, in quotes: "Ana, Jr.",ana@example.org
// When the file can't be read, throw a RuntimeException that names the path.
// Close the file in every case.
//
// Check your work: composer exercise 22 advanced
