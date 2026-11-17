<?php

declare(strict_types=1);

// Exercise 29, advanced: copies that always add up.
//
// Write a class Book. Its constructor takes a title and the number of copies
// the library owns (at least 1, or an InvalidArgumentException). Everyone may
// read the title and the copies on the shelf (copies), but only the book may
// change them. Its methods:
// - lend(): void takes a copy off the shelf, or throws a RuntimeException
//   when none is left;
// - giveBack(): void puts one back, or throws a RuntimeException when all the
//   copies the library owns are already on the shelf;
// - onLoan(): int says how many copies are on loan.
//
// Check your work: composer exercise 29 advanced
