<?php

declare(strict_types=1);

// A hash from years ago, made with a lower cost. At the next login, it is made again with today's settings.

$typed = 'tea kettle under the lemon tree';
$stored = password_hash($typed, PASSWORD_BCRYPT, ['cost' => 10]);
echo 'Stored: ', substr($stored, 0, 7), "\n";

if (password_verify($typed, $stored) && password_needs_rehash($stored, PASSWORD_DEFAULT)) {
    $stored = password_hash($typed, PASSWORD_DEFAULT); // only now do we have the password itself
    echo 'Stored again: ', substr($stored, 0, 7), "\n";
}
echo 'Needs it again: ', password_needs_rehash($stored, PASSWORD_DEFAULT) ? 'yes' : 'no', "\n";
