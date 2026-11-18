<?php

declare(strict_types=1);

namespace Shelf;

use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * A rule says no (chapter 87): what was an InvalidArgumentException with an English text, now with a
 * text any page can show in its language. Still an InvalidArgumentException: every catch keeps working.
 *
 *     throw new Refused('"{title}" is back already.', ['title' => $title]);
 */
class Refused extends InvalidArgumentException implements TranslatableInterface
{
    use Says;
}
