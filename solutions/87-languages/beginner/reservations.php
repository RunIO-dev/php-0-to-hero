<?php

declare(strict_types=1);

// How many reservations wait (chapter 87), in English or German, as one ICU message per language with
// MessageFormatter: 0 is "no reservations" / "keine Reservierungen", 1 is "1 reservation" /
// "1 Reservierung", and any other count "# reservations" / "# Reservierungen". German writes 1234
// as 1.234; # does that for you.

function reservations(string $language, int $count): string
{
    $message = match ($language) {
        'de' => '{count, plural, =0 {keine Reservierungen} one {# Reservierung} other {# Reservierungen}}',
        default => '{count, plural, =0 {no reservations} one {# reservation} other {# reservations}}',
    };

    return (string) MessageFormatter::formatMessage($language, $message, ['count' => $count]);
}
