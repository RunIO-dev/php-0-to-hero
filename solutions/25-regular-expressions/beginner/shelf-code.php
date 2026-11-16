<?php

declare(strict_types=1);

function isShelfCode(string $code): bool
{
    return preg_match('/^[A-Z]{2}-\d{3}$/D', $code) === 1;
}
