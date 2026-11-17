<?php

declare(strict_types=1);

// Exercise 45, beginner: a setting from the environment.
//
// Docker passes settings to a container as environment variables (environment: in
// compose.yaml). Show "Welcome to <name>" and a new line, in plain text, where <name>
// is the variable LIBRARY_NAME, or "Shelf" when it isn't set or is empty.

header('Content-Type: text/plain; charset=UTF-8');

echo "Welcome to Shelf\n";

// Check your work: composer exercise 45 beginner
