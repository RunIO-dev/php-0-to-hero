<?php

declare(strict_types=1);

use Club\NewMember;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

require_once subject(__DIR__, 'NewMember.php');

final class NewMemberTest extends TestCase
{
    public function testAGoodMemberHasNoMistakes(): void
    {
        self::assertSame([], self::mistakes(self::member('Amira', 'amira@example.org', 'tea kettle by the window', 'reader')));
        self::assertSame([], self::mistakes(self::member('Jonas', 'jonas@example.org', 'river stones in the morning', 'staff')));
    }

    public function testEmptyFieldsAreNamed(): void
    {
        self::assertSame([
            'name' => ['A member needs a name.'],
            'email' => ['A member needs an email address.'],
            'password' => ['A password needs at least 15 characters.'],
        ], self::mistakes(self::member('', '', '', 'reader')));
    }

    public function testEachRule(): void
    {
        self::assertSame([
            'name' => ['A name has at most 100 characters.'],
            'email' => ['"ana.example.org" is not an email address.'],
            'password' => ['A password needs at least 15 characters.'],
            'role' => ['The role is reader or staff.'],
        ], self::mistakes(self::member(str_repeat('n', 101), 'ana.example.org', 'short one', 'admin')));
    }

    private static function member(string $name, string $email, string $password, string $role): NewMember
    {
        $member = new NewMember();
        $member->name = $name;
        $member->email = $email;
        $member->password = $password;
        $member->role = $role;

        return $member;
    }

    /** @return array<string, list<string>> */
    private static function mistakes(NewMember $member): array
    {
        $mistakes = [];
        foreach (Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator()->validate($member) as $violation) {
            $mistakes[$violation->getPropertyPath()][] = (string) $violation->getMessage();
        }

        return $mistakes;
    }
}
