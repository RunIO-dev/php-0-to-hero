<?php

declare(strict_types=1);

// Environment variables: settings a program gets from outside, from the system, Docker or the server.
// compose.yaml sets SHELF_APP_PASSWORD for the course's containers (chapter 59).

$password = getenv('SHELF_APP_PASSWORD');
echo 'SHELF_APP_PASSWORD is ', $password === false ? 'not set' : 'set', "\n";

var_dump(getenv('LIBRARY_NAME')); // nobody set it
