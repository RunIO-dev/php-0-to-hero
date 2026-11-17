<?php

declare(strict_types=1);

// Exercise 51, advanced: a second check, where the request came from.

/** @param array<string, string> $headers */
function fromOurSite(array $headers, string $ourOrigin): bool
{
    if (isset($headers['sec-fetch-site'])) {
        return in_array($headers['sec-fetch-site'], ['same-origin', 'none'], true);
    }
    if (isset($headers['origin'])) {
        return $headers['origin'] === $ourOrigin;
    }
    if (isset($headers['referer'])) {
        return originOf($headers['referer']) === $ourOrigin;
    }

    return false;
}

/** Scheme, host and port of a URL, with a default port left out; null if it has none. */
function originOf(string $url): ?string
{
    $parts = parse_url($url);
    if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
        return null;
    }
    $default = ['https' => 443, 'http' => 80][$parts['scheme']] ?? null;
    $port = isset($parts['port']) && $parts['port'] !== $default ? ':' . $parts['port'] : '';

    return "{$parts['scheme']}://{$parts['host']}$port";
}
