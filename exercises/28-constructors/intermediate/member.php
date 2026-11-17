<?php

declare(strict_types=1);

// Exercise 28, intermediate: a member that is always valid.
//
// Write a class Member with two public properties, set by its constructor:
// - name: the name as given, without spaces around it. A name that is empty
//   after trimming is refused with an InvalidArgumentException.
// - maxLoans: how many books the member may borrow at once, 3 when left out.
//   It must be from 1 to 10; anything else is refused the same way.
//
// Check your work: composer exercise 28 intermediate
