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
        // The two dates at midnight in UTC: whole days, whatever the time or summer time.
        $day = fn(DateTimeImmutable $at): DateTimeImmutable => new DateTimeImmutable($at->format('Y-m-d'), new DateTimeZone('UTC'));
        $late = (int) $day($due)->diff($day($returned))->format('%r%a');

        return min(self::MAX, max(0, $late) * self::PER_DAY);
    }

    public static function format(int $cents): string
    {
        return sprintf('%d.%02d €', intdiv($cents, 100), $cents % 100);
    }
}
