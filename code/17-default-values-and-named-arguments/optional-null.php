<?php

declare(strict_types=1);

// Optional, with no sensible default: ?type = null.
function memberLine(string $name, ?string $email = null): string
{
    return $email === null ? $name : "$name <$email>";
}

echo memberLine('Amira'), "\n";
echo memberLine('Jonas', 'jonas@example.org'), "\n";
