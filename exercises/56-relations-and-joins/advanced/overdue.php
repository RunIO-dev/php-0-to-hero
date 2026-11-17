<?php

declare(strict_types=1);

// Exercise 56, advanced: the reminder list. Which loans are overdue on a given day?
//
// overdue($db, '2026-03-17') returns one line per loan whose due date is before that day, the
// longest overdue first: "Beloved: Kenji, 18 days late", or "1 day late". Use a prepared statement
// for the day, and JOIN for the title and the name. Dates are ISO text ('2026-03-17'), so < compares
// them. Count the days in PHP: DateTimeImmutable::diff(), then ->days.
// A day that isn't a date of that shape: InvalidArgumentException, before any query.

/** @return list<string> */
function overdue(PDO $db, string $today): array
{
    return [];
}
// Check your work: composer exercise 56 advanced
