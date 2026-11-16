<?php

declare(strict_types=1);

// There is no 30 February. PHP doesn't complain: it moves on into March.
$date = new DateTimeImmutable('2026-02-30');
echo $date->format('Y-m-d'), "\n";

// To check a date, read it with a fixed format and compare it with the input.
$input = '2026-02-30';
$parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $input);
$isValid = $parsed !== false && $parsed->format('Y-m-d') === $input;
echo $isValid ? "valid\n" : "not a real date\n";
