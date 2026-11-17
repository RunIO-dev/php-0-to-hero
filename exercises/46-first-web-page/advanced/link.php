<?php

declare(strict_types=1);

// Exercise 46, advanced: a link from untrusted data.
//
// Write safeLink(), which returns an <a> element for a URL and a text, both from a visitor:
// - the URL must start with https://, http:// or / (not //, which leaves the site);
//   for anything else, such as javascript:alert(1), return the text alone, escaped;
// - the URL goes into href="...", escaped; the text is escaped too.
// searchLink() returns a link to /search.php?q=<text>, with the text URL-encoded
// (urlencode), then escaped, and the text itself as the link text.

function safeLink(string $url, string $text): string
{
    return "<a href=\"$url\">$text</a>"; // your code here
}

function searchLink(string $text): string
{
    return ''; // your code here
}

// Check your work: composer exercise 46 advanced
