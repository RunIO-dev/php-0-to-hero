<?php

// Find the first day, from day 1, when the library is open and a copy is back.
$closedDay = 2;
$copyBackOnDay = 3;

for ($day = 1; $day <= 7; $day++) {
    if ($day === $closedDay) {
        echo "Day $day: closed\n";
        continue;
    }
    if ($day >= $copyBackOnDay) {
        echo "Day $day: you can borrow it\n";
        break;
    }
    echo "Day $day: still on loan\n";
}
