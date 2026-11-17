<?php

declare(strict_types=1);

// Exercise 46, advanced: a link from untrusted data. A solution.

function safeLink(string $url, string $text): string
{
    $safe = preg_match('~^(https?://|/(?!/))~i', $url) === 1;

    return $safe ? '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($text) . '</a>' : htmlspecialchars($text);
}

function searchLink(string $text): string
{
    return safeLink('/search.php?q=' . urlencode($text), $text);
}
