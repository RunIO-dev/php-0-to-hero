<?php

declare(strict_types=1);

// DateTime (without Immutable) changes itself.
$lentOn = new DateTime('2026-11-17');
$dueOn = $lentOn->modify('+14 days');

echo 'Lent on: ', $lentOn->format('Y-m-d'), "\n";
echo 'Due on:  ', $dueOn->format('Y-m-d'), "\n";
