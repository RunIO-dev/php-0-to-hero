<?php

declare(strict_types=1);

// A date as an object: it knows its day, month, year and weekday.
$lentOn = new DateTimeImmutable('2026-11-17');

echo $lentOn->format('Y-m-d'), "\n";      // ISO format, for files and databases
echo $lentOn->format('l, j F Y'), "\n";   // for people
echo $lentOn->format('d.m.Y'), "\n";      // day.month.year, as in Germany
