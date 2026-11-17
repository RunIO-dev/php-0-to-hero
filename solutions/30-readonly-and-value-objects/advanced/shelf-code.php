<?php

declare(strict_types=1);

final readonly class ShelfCode
{
    public string $code;

    public function __construct(string $input)
    {
        $code = strtoupper(trim($input));
        if (preg_match('/^[A-Z]{2}-\d{3}$/D', $code) !== 1) {
            throw new InvalidArgumentException("\"$input\" is not a shelf code");
        }
        $this->code = $code;
    }

    public function section(): string
    {
        return substr($this->code, 0, 2);
    }

    public function number(): int
    {
        return (int) substr($this->code, 3);
    }

    public function equals(ShelfCode $other): bool
    {
        return $this->code === $other->code;
    }
}
