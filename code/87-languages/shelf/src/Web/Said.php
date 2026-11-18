<?php

declare(strict_types=1);

namespace Shelf\Web;

use Symfony\Contracts\Translation\TranslatableInterface;
use Throwable;

/**
 * What a page says about a refusal (chapter 87). Shelf's own refusals (Refused, ShelfException) are
 * translatable: the page gets them as they are, and |trans says them in its language. Any other
 * exception gets its English message; never the exception itself, whose text is its whole trace.
 */
final class Said
{
    public static function of(Throwable $e): TranslatableInterface|string
    {
        return $e instanceof TranslatableInterface ? $e : $e->getMessage();
    }
}
