<?php

declare(strict_types=1);

final readonly class Money
{
    public function __construct(public int $cents) {}
}

$a = new Money(200);
$b = new Money(200);

// === asks "the same object?", == asks "same class and equal properties?".
var_dump($a === $b);
var_dump($a == $b);
