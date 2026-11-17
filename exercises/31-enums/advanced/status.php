<?php

declare(strict_types=1);

// Exercise 31, advanced: the status of a loan.
//
// Write an enum LoanStatus with four cases: Returned, OnTime, DueSoon and
// Overdue, and two methods:
// - label(): string: "returned", "on loan", "due soon", "overdue";
// - needsReminder(): bool: true for DueSoon and Overdue only.
//
// Then write a function
//   statusOf(DateTimeImmutable $due, DateTimeImmutable $today, bool $returned): LoanStatus
// - a returned book is Returned, whatever the dates;
// - after the due date, it is Overdue;
// - on the due date or up to 3 days before it, DueSoon;
// - before that, OnTime.
//
// Check your work: composer exercise 31 advanced
