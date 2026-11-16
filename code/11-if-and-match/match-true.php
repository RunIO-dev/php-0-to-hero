<?php

$daysLate = 9;

$reminder = match (true) {
    $daysLate <= 0 => 'none',
    $daysLate <= 7 => 'a friendly email',
    default => 'a letter',
};

echo "Reminder: $reminder\n";
