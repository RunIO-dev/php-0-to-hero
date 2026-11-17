<?php

declare(strict_types=1);

// Exercise 29, beginner: a library card with fees.
//
// Write a class Card with a property balance: the fees owed, in cents. It
// starts at 0. Everyone may read it, but only the card's own methods may
// change it:
// - charge(int $cents): void adds a fee;
// - pay(int $cents): void takes a payment off the balance.
// Both refuse an amount of 0 or less with an InvalidArgumentException, and pay
// also refuses to take more than is owed.
//
// Check your work: composer exercise 29 beginner
