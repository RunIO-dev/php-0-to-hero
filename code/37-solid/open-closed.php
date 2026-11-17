<?php

declare(strict_types=1);

// O: add a new rule by adding a class, without changing Checkout.
interface Discount
{
    public function apply(int $cents): int;
}

final readonly class MemberDiscount implements Discount
{
    public function apply(int $cents): int
    {
        return intdiv($cents * 90, 100);
    }
}

final readonly class Cap implements Discount
{
    public function __construct(private int $max) {}

    public function apply(int $cents): int
    {
        return min($cents, $this->max);
    }
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

echo (new Checkout([new MemberDiscount()]))->total(2000), "\n";
echo (new Checkout([new MemberDiscount(), new Cap(1500)]))->total(2000), "\n";
