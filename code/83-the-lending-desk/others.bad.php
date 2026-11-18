<?php

declare(strict_types=1);

// ✗ "Extend" for whichever loan number the form sends: a reader changes the number in the form,
// or sends the request by hand, and extends someone else's loan. A hidden field is input too.

function renew(PDO $db, int $loan): void
{
    $db->prepare("UPDATE loans SET due = date(due, '+14 days') WHERE id = ?")->execute([$loan]);
}
