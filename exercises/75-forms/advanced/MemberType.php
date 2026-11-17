<?php

declare(strict_types=1);

namespace Club;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The sign-up form for NewMember (next to this file): fields name (text), email, password, and role,
 * a choice of "Reader" => reader and "Staff" => staff. Only staff may give a role: with the option
 * may_choose_role false (the default), the form has no role field at all, so a visitor can't send one.
 */
final class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class)->add('email', TextType::class)->add('password')->add('role');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => NewMember::class]); // TODO: the option may_choose_role
    }
}
