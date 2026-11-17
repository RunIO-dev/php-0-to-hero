<?php

declare(strict_types=1);

// Exercise 45, advanced: who can connect?
//
// A port mapping in compose.yaml is "host:container" or "address:host:container".
// Write exposure(), which says who can reach the container's port:
//   "127.0.0.1:8000:8000"  =>  "only this computer, on port 8000"
//   "[::1]:8080:80"        =>  "only this computer, on port 8080"
//   "8000:8000"            =>  "every network, on port 8000"
//   "0.0.0.0:8000:8000"    =>  "every network, on port 8000"
// Anything else (one number, letters, a port above 65535) throws an InvalidArgumentException.

function exposure(string $mapping): string
{
    return ''; // your code here
}

// Check your work: composer exercise 45 advanced
