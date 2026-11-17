<?php

declare(strict_types=1);

// Exercise 51, advanced: a second check, where the request came from.
//
// Browsers tell the server which site a request comes from. fromOurSite($headers, $ourOrigin)
// says whether a POST came from our own pages. $headers has lowercase names; $ourOrigin is
// scheme, host and port, such as 'https://shelf.example'. In this order:
// 1. sec-fetch-site, if sent: 'same-origin' is ours, and so is 'none' (the visitor typed the
//    address). Everything else ('same-site', 'cross-site') is not.
// 2. Otherwise origin, if sent: it must be exactly $ourOrigin. The value 'null' is not ours.
// 3. Otherwise referer, if sent: its scheme, host and port (parse_url) must give $ourOrigin.
//    A default port (443 for https, 80 for http) is not written in an origin.
// 4. No header at all: false. A POST that can't say where it comes from is refused.
// Compare whole origins: 'https://shelf.example.stranger.example' is not 'https://shelf.example'.

/** @param array<string, string> $headers */
function fromOurSite(array $headers, string $ourOrigin): bool
{
    return true;
}
// Check your work: composer exercise 51 advanced
