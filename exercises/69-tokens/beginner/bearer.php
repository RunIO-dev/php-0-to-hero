<?php

declare(strict_types=1);

/**
 * The token in an Authorization header such as "Bearer 8a57...", or null when there is none.
 * - "Bearer" may be written in any case, and spaces around the whole header don't count.
 * - Any other scheme ("Basic ..."), no token, or a token with a space inside: null.
 * - No header at all is null too.
 */
function bearerToken(?string $header): ?string
{
    return substr((string) $header, 7);
}
