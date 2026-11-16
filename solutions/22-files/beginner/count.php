<?php

declare(strict_types=1);

function countTitles(string $path): int
{
    return count(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []);
}
