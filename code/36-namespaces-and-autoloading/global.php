<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;

// PHP's own classes live in the global namespace: use them, or write \DateTimeImmutable.
$lent = new DateTimeImmutable('2026-11-16');
$due = new \DateTimeImmutable('2026-11-30');

// A function that the namespace doesn't have falls back to the global one, such as strlen.
echo $lent->diff($due)->days, " days\n";
echo strlen('Kindred'), " letters\n";
