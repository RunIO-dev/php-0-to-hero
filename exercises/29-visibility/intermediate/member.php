<?php

declare(strict_types=1);

// Exercise 29, intermediate: a member's loans, kept private.
//
// Write a class Member with a public name, set by the constructor, and a
// private list of the titles the member has borrowed. Its methods:
// - borrow(string $title): void adds a title. A member may have 3 at most;
//   a fourth is refused with a RuntimeException.
// - giveBack(string $title): void removes the title. A title the member
//   doesn't have is refused with a RuntimeException.
// - titles(): array returns the borrowed titles, in the order borrowed.
//
// Check your work: composer exercise 29 intermediate
