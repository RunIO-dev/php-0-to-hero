<?php

declare(strict_types=1);

// Exercise 24, intermediate: a reading plan, with input you don't trust.
//
//   php plan.php 320 30    prints   320 pages at 30 a day: 11 days
// Both numbers must be whole numbers from 1 to 10000. Otherwise, or when one
// is missing, print to STDERR and exit(1):
//   Pages and pages per day must be whole numbers from 1 to 10000.
// Tip: filter_var() with FILTER_VALIDATE_INT and a range; (int) accepts anything.
//
// Check your work: composer exercise 24 intermediate
