<?php

declare(strict_types=1);

// Exercise 16, beginner: add the types.
//
// Both functions work, but they don't say what they take and what they give
// back. Add a type to every parameter and a return type to both functions:
// the page numbers are whole numbers, and isFinished() answers yes or no.
//
// Check your work: composer exercise 16 beginner

function pagesLeft($total, $read)
{
    return $total - $read;
}

function isFinished($total, $read)
{
    return pagesLeft($total, $read) <= 0;
}
