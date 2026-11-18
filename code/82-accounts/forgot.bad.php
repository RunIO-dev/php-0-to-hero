<?php

declare(strict_types=1);

// ✗ A "forgot your password" page that tells the truth about every address:
// anyone can type addresses and learn who has an account at the library.

function forgot(?array $member): string
{
    if ($member === null) {
        return 'There is no account with this address.';
    }

    return 'We sent you a link.';
}
