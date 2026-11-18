<?php

declare(strict_types=1);

// The fine for a book back late (chapter 83), in cents, so no money is ever a float.
//   Fine::for($due, $returned): 25 cents for each day after the due date, at most 1000 (10.00 €).
//   Only the dates count, never the time: back at 23:59 on the due date is on time.
//   Fine::format(250) === '2.50 €', Fine::format(5) === '0.05 €', Fine::format(0) === '0.00 €'.

final class Fine
{
    public const int PER_DAY = 25;
    public const int MAX = 1000;

    public static function for(DateTimeImmutable $due, DateTimeImmutable $returned): int
    {
        return 0; // TODO
    }

    public static function format(int $cents): string
    {
        return "$cents"; // TODO
    }
}
