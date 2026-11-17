<?php

declare(strict_types=1);

// Exercise 34, beginner: a family of exceptions.
//
// Write three exception classes:
// - LibraryException, which extends RuntimeException, for every problem of
//   the library;
// - MemberNotFound and CardExpired, which both extend LibraryException, and
//   which no other class may extend.
// Then write a function checkCard(string $name, bool $expired): void that
// throws MemberNotFound when the name is empty, and CardExpired when the card
// has expired, each with a message.
//
// Check your work: composer exercise 34 beginner
