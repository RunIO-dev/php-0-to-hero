<?php

declare(strict_types=1);

interface Discount
{
    public function apply(int $cents): int;
}

final readonly class Checkout
{
    /** @param list<Discount> $discounts */
    public function __construct(private array $discounts) {}

    public function total(int $cents): int
    {
        foreach ($this->discounts as $discount) {
            $cents = $discount->apply($cents);
        }

        return $cents;
    }
}

final readonly class StudentDiscount implements Discount
{
    public function apply(int $cents): int
    {
        return intdiv($cents * 80, 100);
    }
}

final readonly class Voucher implements Discount
{
    public function __construct(private int $off) {}

    public function apply(int $cents): int
    {
        return max(0, $cents - $this->off);
    }
}
