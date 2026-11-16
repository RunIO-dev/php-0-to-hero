<?php

declare(strict_types=1);

// The regex checks the shape; arithmetic checks the last digit.
function isIsbn13(string $input): bool
{
    $digits = str_replace(['-', ' '], '', $input);
    if (preg_match('/^97[89]\d{10}$/D', $digits) !== 1) {
        return false;
    }
    $sum = 0;
    foreach (str_split(substr($digits, 0, 12)) as $i => $digit) {
        $sum += (int) $digit * ($i % 2 === 0 ? 1 : 3);
    }

    return (10 - $sum % 10) % 10 === (int) $digits[12];
}

foreach (['978-0-306-40615-7', '978-0-306-40615-8', '978 3 16 148410 0', '12345'] as $isbn) {
    echo str_pad($isbn, 20), isIsbn13($isbn) ? 'valid' : 'invalid', "\n";
}
