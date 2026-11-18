<?php

declare(strict_types=1);

// Whether a loan may be extended (chapter 83), and if not, why. Dates are "Y-m-d" strings.
//   null, if it may;
//   'overdue', from the day after the due date;
//   'too often', once it was extended twice.
// Overdue is checked first: an overdue loan that was extended twice is 'overdue'.

function renewalProblem(string $due, string $today, int $renewals): ?string
{
    return null; // TODO
}
