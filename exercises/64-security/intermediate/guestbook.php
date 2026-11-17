<?php

declare(strict_types=1);

// Exercise 64, intermediate: two holes from the OWASP list. Close them.
//
// - entryHtml($name, $text): one entry of the guest book as HTML, <li><b>name</b>: text</li>.
//   Visitors write both values.
// - afterLogin($next): where to send the visitor after logging in. $next comes from the address
//   (/login?next=/loans), so the visitor came back where they were. Only a path on this site is
//   allowed: it starts with one "/", and the next character is neither "/" nor "\". Anything
//   else, such as https://stranger.example or //stranger.example, gives "/".

function entryHtml(string $name, string $text): string
{
    return "<li><b>$name</b>: $text</li>";
}

function afterLogin(string $next): string
{
    return $next;
}
// Check your work: composer exercise 64 intermediate
