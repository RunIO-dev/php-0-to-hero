<?php

declare(strict_types=1);

// Exercise 35, intermediate: a report with fixed steps.
//
// Every report of the library has the same frame: a title line, one line per
// row, and a line that counts the rows. Write an abstract class Report:
// - a final public method render(): string that builds the whole text:
//     == <title> ==
//     <one line per row>
//     (<n> rows)
//   with a line break after each line, and "1 row" for one row;
// - two abstract methods for the children: title(): string and
//   rows(): array (a list of strings).
// Then write two classes that extend it:
// - TitlesReport, whose constructor takes a list of titles, with the title
//   "Titles";
// - LoansReport, whose constructor takes a map from title to member name, with
//   the title "Loans" and rows such as "Kindred: Amira".
//
// Check your work: composer exercise 35 intermediate
