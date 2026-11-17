<?php

declare(strict_types=1);

// bcrypt, PHP's default algorithm, reads only the first 72 bytes of a password.

$start = str_repeat('my favourite book is Kindred ', 3); // 87 bytes
$hash = password_hash($start . 'and Sula', PASSWORD_DEFAULT);

echo strlen($start), " bytes, then the end differs\n";
var_dump(password_verify($start . 'and nothing else', $hash));
