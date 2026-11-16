<?php

// Exercise 11, advanced: the late fee rule.
//
// The library's rule for a book returned $daysLate days after its due date:
//   on time or early (0 days or less):  Returned on time
//   1 to 10 days late:                  Late fee: 0.25  (25 cents a day)
//   more than 10 days:                  Late fee: 2.50 (the maximum)
// Print the right line. Calculate in cents (chapter 9), and print the amount
// with two decimals. The tests try several numbers of days.
//
// Check your work: composer exercise 11 advanced

$daysLate = 4;
