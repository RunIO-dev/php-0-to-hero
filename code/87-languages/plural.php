<?php

declare(strict_types=1);

// One message, every count (chapter 87). ICU MessageFormat, in PHP's intl extension, picks the case
// that the language's rules name for the number: English has one and other; Arabic has six, from zero
// to other. # is the number, written as that language writes it.

$messages = [
    'en' => '{count, plural, one {# book} other {# books}}',
    'de' => '{count, plural, one {# Buch} other {# Bücher}}',
    'ar' => '{count, plural, zero {لا كتب} one {كتاب واحد} two {كتابان} few {# كتب} many {# كتابًا} other {# كتاب}}',
];

foreach ($messages as $language => $message) {
    $line = [];
    foreach ([0, 1, 2, 3, 11, 100] as $count) {
        $line[] = MessageFormatter::formatMessage($language, $message, ['count' => $count]);
    }
    echo $language, ': ', implode(' | ', $line), "\n";
}

// A date in each language's words and order, and a number with its separators.
$day = new DateTimeImmutable('2026-03-16');
foreach (['en', 'de', 'ar'] as $language) {
    echo $language, ': ', MessageFormatter::formatMessage($language, 'due back on {day, date, full}, {n, number} readers', ['day' => $day, 'n' => 1234]), "\n";
}
