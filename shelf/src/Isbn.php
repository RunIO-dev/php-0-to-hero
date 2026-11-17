<?php

declare(strict_types=1);

namespace Shelf;

use InvalidArgumentException;

/** An ISBN-13, valid from the start: the regex checks the shape, arithmetic the last digit. */
final readonly class Isbn
{
    public string $digits;

    public function __construct(string $input)
    {
        $digits = str_replace(['-', ' '], '', $input);
        if (!$this->isValid($digits)) {
            throw new InvalidArgumentException("\"$input\" is not a valid ISBN-13");
        }
        $this->digits = $digits;
    }

    public function equals(Isbn $other): bool
    {
        return $this->digits === $other->digits;
    }

    private function isValid(string $digits): bool
    {
        if (preg_match('/^97[89]\d{10}$/D', $digits) !== 1) {
            return false;
        }
        $sum = 0;
        foreach (str_split(substr($digits, 0, 12)) as $i => $digit) {
            $sum += (int) $digit * ($i % 2 === 0 ? 1 : 3);
        }

        return (10 - $sum % 10) % 10 === (int) $digits[12];
    }
}
