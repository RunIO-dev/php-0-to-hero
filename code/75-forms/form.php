<?php

declare(strict_types=1);

// A form (symfony/form): its fields, the class it fills, and the validator behind it. submit() takes
// what a browser sends, which is always text; the form turns it into the field's type.

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Review.php';

$validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
$forms = Forms::createFormFactoryBuilder()->addExtension(new ValidatorExtension($validator))->getFormFactory();

function reviewForm(Symfony\Component\Form\FormFactoryInterface $forms): Symfony\Component\Form\FormInterface
{
    return $forms->createBuilder(options: ['data_class' => Review::class])
        ->add('book', TextType::class)
        ->add('stars', IntegerType::class, ['invalid_message' => 'Stars are a number.'])
        ->add('text', TextareaType::class, ['required' => false])
        ->getForm();
}

$form = reviewForm($forms);
$form->submit(['book' => 'Kindred', 'stars' => 'five', 'admin' => '1']);
var_dump($form->isValid());
foreach ($form->getErrors(deep: true) as $error) {
    echo '- ', $error->getMessage(), "\n";
}

$form = reviewForm($forms);
$form->submit(['book' => 'Kindred', 'stars' => '5', 'text' => 'Read it in one night.']);
var_dump($form->isValid());
$review = $form->getData();
assert($review instanceof Review);
var_dump($review->stars); // a number now
