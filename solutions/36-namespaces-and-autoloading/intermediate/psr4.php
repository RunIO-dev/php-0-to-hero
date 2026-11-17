<?php

declare(strict_types=1);

function classFile(string $class, string $prefix, string $dir): ?string
{
    if (!str_starts_with($class, $prefix)) {
        return null;
    }
    $rest = substr($class, strlen($prefix));

    return $dir . '/' . str_replace('\\', '/', $rest) . '.php';
}
