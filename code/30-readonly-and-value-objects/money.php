<?php

declare(strict_types=1);

// A value object: an amount of money, valid from the start, never changed.
final readonly class Money
{
    public function __construct(public int $cents)
    {
        if ($cents < 0) {
            throw new InvalidArgumentException("An amount can't be $cents cents");
        }
    }

    // A change gives a new object; this one stays as it is.
    public function add(Money $other): Money
    {
        return new Money($this->cents + $other->cents);
    }

    public function equals(Money $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function format(): string
    {
        return number_format($this->cents / 100, 2) . ' EUR';
    }
}

$fee = new Money(150);
$total = $fee->add(new Money(50));

echo $fee->format(), ' + 0.50 EUR = ', $total->format(), "\n";
var_dump($total->equals(new Money(200)));
