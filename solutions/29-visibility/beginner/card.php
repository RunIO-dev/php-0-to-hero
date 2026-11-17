<?php

declare(strict_types=1);

final class Card
{
    public private(set) int $balance = 0;

    public function charge(int $cents): void
    {
        $this->checkPositive($cents);
        $this->balance += $cents;
    }

    public function pay(int $cents): void
    {
        $this->checkPositive($cents);
        if ($cents > $this->balance) {
            throw new InvalidArgumentException("Only $this->balance cents are owed");
        }
        $this->balance -= $cents;
    }

    private function checkPositive(int $cents): void
    {
        if ($cents <= 0) {
            throw new InvalidArgumentException('An amount must be more than 0 cents');
        }
    }
}
