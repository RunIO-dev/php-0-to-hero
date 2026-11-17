<?php

declare(strict_types=1);

trait FormatsMoney
{
    public function format(int $cents): string
    {
        return number_format($cents / 100, 2) . ' EUR';
    }
}

trait FormatsDates
{
    public function format(DateTimeImmutable $day): string
    {
        return $day->format('j F Y');
    }
}

final class Receipt
{
    use FormatsMoney;
    use FormatsDates;
}
