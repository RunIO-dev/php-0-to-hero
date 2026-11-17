<?php

declare(strict_types=1);

// Exercise 32, intermediate: card numbers with named constructors.
//
// A member card number is "MC-" and six digits: MC-000124. Write a readonly
// class CardNumber with a public string property number and a private
// constructor, so that cards are made only through two static methods:
// - fromString(string $input): self accepts a number as typed, with spaces
//   around it, or throws an InvalidArgumentException;
// - after(CardNumber $last): self returns the next number: after MC-000124
//   comes MC-000125.
//
// Check your work: composer exercise 32 intermediate
