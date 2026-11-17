<?php

declare(strict_types=1);

use Club\MemberType;
use Club\NewMember;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

require_once subject(__DIR__, 'NewMember.php');
require_once subject(__DIR__, 'MemberType.php');

final class MemberTypeTest extends TestCase
{
    private const array AMIRA = ['name' => 'Amira', 'email' => 'amira@example.org', 'password' => 'tea kettle by the window'];

    public function testAVisitorSignsUpAsAReader(): void
    {
        $form = self::forms()->create(MemberType::class);
        $form->submit(self::AMIRA);

        self::assertTrue($form->isValid());
        $member = $form->getData();
        self::assertInstanceOf(NewMember::class, $member);
        self::assertSame(['Amira', 'amira@example.org', 'reader'], [$member->name, $member->email, $member->role]);
    }

    public function testAVisitorCantSendARole(): void
    {
        $form = self::forms()->create(MemberType::class);
        $form->submit([...self::AMIRA, 'role' => 'staff']);

        self::assertFalse($form->isValid());
        self::assertSame(['This form should not contain extra fields.'], self::messages($form->getErrors(deep: true)));
        self::assertFalse($form->has('role'));
    }

    public function testStaffChooseTheRole(): void
    {
        $form = self::forms()->create(MemberType::class, options: ['may_choose_role' => true]);
        $form->submit([...self::AMIRA, 'role' => 'staff']);

        self::assertTrue($form->isValid());
        self::assertSame('staff', $form->getData()?->role);
    }

    public function testOnlyTheOfferedRoles(): void
    {
        $form = self::forms()->create(MemberType::class, options: ['may_choose_role' => true]);
        $form->submit([...self::AMIRA, 'role' => 'admin']);

        self::assertFalse($form->isValid());
        self::assertFalse($form->get('role')->isValid());
    }

    public function testTheRulesOfNewMemberApply(): void
    {
        $form = self::forms()->create(MemberType::class);
        $form->submit(['name' => '', 'email' => 'nobody', 'password' => 'short']);

        self::assertFalse($form->isValid());
        self::assertSame(['A member needs a name.'], self::messages($form->get('name')->getErrors()));
        self::assertSame(['"nobody" is not an email address.'], self::messages($form->get('email')->getErrors()));
        self::assertSame(['A password needs at least 15 characters.'], self::messages($form->get('password')->getErrors()));
    }

    public function testThePasswordFieldNeverShowsThePassword(): void
    {
        $form = self::forms()->create(MemberType::class);
        $form->submit([...self::AMIRA, 'password' => 'short']);

        self::assertSame('', $form->get('password')->createView()->vars['value']);
    }

    private static function forms(): FormFactoryInterface
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        return Forms::createFormFactoryBuilder()->addExtension(new ValidatorExtension($validator))->getFormFactory();
    }

    /** @param iterable<Symfony\Component\Form\FormError> $errors */
    private static function messages(iterable $errors): array
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getMessage();
        }

        return $messages;
    }
}
