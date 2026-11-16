<?php

// return ends the function at once. The first return wins.
function dueText($daysLeft)
{
    if ($daysLeft < 0) {
        return 'overdue';
    }
    if ($daysLeft === 0) {
        return 'due today';
    }

    return "due in $daysLeft days";
}

echo dueText(5), "\n";
echo dueText(0), "\n";
echo dueText(-2), "\n";
