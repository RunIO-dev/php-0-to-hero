<?php

declare(strict_types=1);

function money(int $cents): string
{
    return number_format($cents / 100, 2) . ' EUR';
}

echo 'Paid: ', money(1250), "\n";
