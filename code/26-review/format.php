<?php

declare(strict_types=1);

// Functions only: this file is loaded by others, and prints nothing itself.

function money(int $cents): string
{
    return number_format($cents / 100, 2) . ' EUR';
}

function plural(int $count, string $word): string
{
    return $count === 1 ? "1 $word" : "$count {$word}s";
}
