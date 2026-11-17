<?php

declare(strict_types=1);

// Exercise 37, intermediate: open for new rules, closed for changes.
//
// Checkout applies a list of discounts, one after the other. Don't change
// Checkout or Discount. Add two rules, each as a new class:
// - StudentDiscount: 20% off, rounded down to whole cents;
// - Voucher: a fixed amount in cents off, given to its constructor; the total
//   never goes below 0.

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

// Check your work: composer exercise 37 intermediate
