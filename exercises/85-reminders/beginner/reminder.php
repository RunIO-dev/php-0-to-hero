<?php

declare(strict_types=1);

// Which reminder a loan needs today (chapter 85). Dates are "Y-m-d" strings; $last is the day of the
// last reminder, or null if none was sent.
//   'due soon': from 2 days before the due date to the due date itself, unless one was sent since
//               those 2 days began;
//   'overdue':  from the day after the due date, if the last reminder was sent on or before the due
//               date (or never), or 7 days or more ago;
//   null:       nothing to send.
// Tip: "Y-m-d" strings compare like dates; date('Y-m-d', strtotime("$day -2 days")) counts days.

function reminderFor(string $due, string $today, ?string $last): ?string
{
    return null; // TODO
}
