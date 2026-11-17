<?php

declare(strict_types=1);

// A new token: 32 random bytes, written as 64 hexadecimal characters. The member gets it once;
// the database keeps its SHA-256, and finds it again by hashing what the next request sends.

$token = bin2hex(random_bytes(32));
$stored = hash('sha256', $token);

echo 'The member gets: ', strlen($token), " characters, shown once\n";
echo 'The database keeps: ', strlen($stored), " characters, the token's SHA-256\n";
echo 'The next request finds it: ', hash('sha256', $token) === $stored ? 'yes' : 'no', "\n";
echo 'A token one character off: ', hash('sha256', substr($token, 0, -1) . 'x') === $stored ? 'yes' : 'no', "\n";
