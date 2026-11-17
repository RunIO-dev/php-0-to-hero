<?php

declare(strict_types=1);

// password_hash: a slow hash with a random salt, made for storing passwords.

$password = 'tea kettle under the lemon tree';
$hash = password_hash($password, PASSWORD_DEFAULT);
$again = password_hash($password, PASSWORD_DEFAULT);

echo $hash === $password ? 'The password itself' : 'A hash', ', ', strlen($hash), " characters\n";
echo 'It starts with: ', substr($hash, 0, 7), "\n";
echo 'The same password, hashed again, gives the same hash: ', $hash === $again ? 'yes' : 'no', "\n";
var_dump(password_get_info($hash));
