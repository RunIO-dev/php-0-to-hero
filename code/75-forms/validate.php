<?php

declare(strict_types=1);

// The validator (symfony/validator) reads the rules from the attributes, and checks every one.

use Symfony\Component\Validator\Validation;

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Review.php';

$validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

$review = new Review();
$review->stars = 7;
$review->email = 'amira.example.org';

foreach ($validator->validate($review) as $violation) {
    echo $violation->getPropertyPath(), ': ', $violation->getMessage(), "\n";
}

$review->book = 'Kindred';
$review->stars = 5;
$review->email = 'amira@example.org';
echo count($validator->validate($review)), " mistakes\n";
