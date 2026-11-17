<?php

declare(strict_types=1);

// A trait: methods that PHP copies into every class that uses it.
trait FormatsMoney
{
    private function money(int $cents): string
    {
        return number_format($cents / 100, 2) . ' EUR';
    }
}

final readonly class Receipt
{
    use FormatsMoney;

    public function __construct(private int $paid) {}

    public function line(): string
    {
        return 'Paid: ' . $this->money($this->paid);
    }
}

final readonly class FeeNotice
{
    use FormatsMoney;

    public function __construct(private int $owed) {}

    public function line(): string
    {
        return 'Please pay ' . $this->money($this->owed);
    }
}

echo (new Receipt(150))->line(), "\n";
echo (new FeeNotice(1000))->line(), "\n";
