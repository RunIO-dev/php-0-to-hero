<?php

declare(strict_types=1);

namespace Shelf\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form to add a book (chapter 75): its fields, and the class it fills, BookData. The form reads
 * the request, turns each field into its type (the year into a number), and has BookData checked.
 */
final class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['attr' => ['maxlength' => 200]])
            ->add('author', TextType::class, ['attr' => ['maxlength' => 200]])
            ->add('year', IntegerType::class, ['invalid_message' => 'The year is a number, such as 1979.', 'attr' => ['inputmode' => 'numeric']])
            ->add('isbn', TextType::class, ['required' => false])
            ->add('cover', FileType::class, ['required' => false, 'attr' => ['accept' => 'image/png, image/jpeg']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BookData::class,
            // The same token as every other form of Shelf's, in the same field: CheckCsrf reads it too.
            'csrf_token_id' => 'shelf',
            'csrf_field_name' => '_token',
        ]);
    }

    /** No name: the fields are sent as title, author and year, not book[title]. */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
