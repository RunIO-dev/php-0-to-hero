<?php

declare(strict_types=1);

namespace Shelf\Tests\Validator;

use Shelf\Validator\ValidIsbn;
use Shelf\Validator\ValidIsbnValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * Symfony's own test case for a constraint validator: it gives the validator a context, and records
 * what it reports.
 *
 * @extends ConstraintValidatorTestCase<ValidIsbnValidator>
 */
final class ValidIsbnValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ValidIsbnValidator
    {
        return new ValidIsbnValidator();
    }

    public function testAValidIsbnPasses(): void
    {
        $this->validator->validate('978-0-8070-8369-7', new ValidIsbn());

        $this->assertNoViolation();
    }

    public function testNoIsbnPasses(): void
    {
        $this->validator->validate(null, new ValidIsbn());
        $this->validator->validate('', new ValidIsbn());

        $this->assertNoViolation();
    }

    public function testAWrongCheckDigitFails(): void
    {
        $this->validator->validate('9780807083698', new ValidIsbn());

        $this->buildViolation('{{ value }} is not a valid ISBN-13.')->setParameter('{{ value }}', '"9780807083698"')->assertRaised();
    }
}
