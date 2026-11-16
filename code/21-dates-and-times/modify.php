<?php

declare(strict_types=1);

$lentOn = new DateTimeImmutable('2026-11-17');

// modify() returns a new date. $lentOn itself never changes.
$dueOn = $lentOn->modify('+14 days');
$reminder = $dueOn->modify('-2 days');

echo 'Lent on:  ', $lentOn->format('Y-m-d'), "\n";
echo 'Reminder: ', $reminder->format('Y-m-d'), "\n";
echo 'Due on:   ', $dueOn->format('Y-m-d'), "\n";
