<?php

declare(strict_types=1);

// A key in the code: everyone who can read the code, now or later, can use it.
const MAIL_KEY = 'mk_4f9a1c7e2b8d6035a1f4c9e7b2d8a6c3';

echo 'Sending reminders with key ', substr(MAIL_KEY, 0, 3), "...\n";
