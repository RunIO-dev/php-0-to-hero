<?php

declare(strict_types=1);

// Exercise 64, intermediate: two holes from the OWASP list. Close them.

function entryHtml(string $name, string $text): string
{
    $e = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES);

    return "<li><b>{$e($name)}</b>: {$e($text)}</li>";
}

function afterLogin(string $next): string
{
    return preg_match('~^/(?![/\\\\])~', $next) === 1 ? $next : '/';
}
