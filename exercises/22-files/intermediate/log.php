<?php

declare(strict_types=1);

// Exercise 22, intermediate: a log of returns.
//
// Write a function logReturn(string $path, string $title, DateTimeImmutable $on): void
// that adds one line to the end of the file, and creates the file if it
// doesn't exist yet. Each line is the date and the title:
//   2026-11-17 Kindred
// Earlier lines must stay.
//
// Check your work: composer exercise 22 intermediate
