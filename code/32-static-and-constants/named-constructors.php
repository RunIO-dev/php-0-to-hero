<?php

declare(strict_types=1);

final readonly class Money
{
    // private: other code must use the named constructors below.
    private function __construct(public int $cents) {}

    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromEuros(string $euros): self
    {
        if (preg_match('/^\d+(\.\d{1,2})?$/D', $euros) !== 1) {
            throw new InvalidArgumentException("\"$euros\" is not an amount in euros");
        }

        [$whole, $cents] = array_pad(explode('.', $euros), 2, '0');

        return new self((int) $whole * 100 + (int) str_pad($cents, 2, '0'));
    }

    public function format(): string
    {
        return number_format($this->cents / 100, 2) . ' EUR';
    }
}

echo Money::zero()->format(), "\n";
echo Money::fromEuros('1.5')->format(), "\n";
echo Money::fromEuros('12.30')->cents, " cents\n";
