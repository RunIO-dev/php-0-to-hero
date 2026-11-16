<?php

declare(strict_types=1);

function isValidDate(string $input): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $input);

    return $date !== false && $date->format('Y-m-d') === $input;
}
