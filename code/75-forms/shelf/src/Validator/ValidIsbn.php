<?php

declare(strict_types=1);

namespace Shelf\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * A constraint of our own (chapter 75): the value is empty, or an ISBN-13 that Shelf's Isbn accepts.
 * ValidIsbnValidator does the checking; the attribute only names the rule and its message.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ValidIsbn extends Constraint
{
    public string $message = '{{ value }} is not a valid ISBN-13.';
}
