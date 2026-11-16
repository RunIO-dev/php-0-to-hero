<?php

declare(strict_types=1);

function logReturn(string $path, string $title, DateTimeImmutable $on): void
{
    file_put_contents($path, $on->format('Y-m-d') . " $title\n", FILE_APPEND);
}
