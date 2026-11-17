<?php

declare(strict_types=1);

class LibraryException extends RuntimeException {}

final class MemberNotFound extends LibraryException {}

final class CardExpired extends LibraryException {}

function checkCard(string $name, bool $expired): void
{
    if (trim($name) === '') {
        throw new MemberNotFound('There is no member without a name');
    }
    if ($expired) {
        throw new CardExpired("The card of $name has expired");
    }
}
