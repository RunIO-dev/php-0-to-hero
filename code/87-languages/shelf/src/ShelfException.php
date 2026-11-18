<?php

declare(strict_types=1);

namespace Shelf;

use RuntimeException;
use Symfony\Contracts\Translation\TranslatableInterface;

/** Every problem the library reports to the user. Chapter 87: in the user's language (Says). */
class ShelfException extends RuntimeException implements TranslatableInterface
{
    use Says;
}
