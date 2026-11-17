<?php

declare(strict_types=1);

namespace Shelf\Validator;

use InvalidArgumentException;
use Shelf\Isbn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/** Checks ValidIsbn with the rule Isbn already has, so the rule stays in one place. */
final class ValidIsbnValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidIsbn) {
            throw new UnexpectedTypeException($constraint, ValidIsbn::class);
        }
        if ($value === null || $value === '') {
            return; // the ISBN is optional; NotBlank is the rule for a field that must be there
        }
        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }
        try {
            new Isbn($value);
        } catch (InvalidArgumentException) {
            $this->context->buildViolation($constraint->message)->setParameter('{{ value }}', $this->formatValue($value))->addViolation(); // in quotes
        }
    }
}
