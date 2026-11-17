<?php

declare(strict_types=1);

// The key comes from the environment of the computer that runs the program, not from the code.
$key = getenv('SHELF_MAIL_KEY');
if ($key === false || $key === '') {
    fwrite(STDERR, "Set SHELF_MAIL_KEY first: the key for sending reminders.\n");
    exit(1);
}

echo 'Sending reminders with key ', substr($key, 0, 3), "...\n";
