<?php

declare(strict_types=1);

namespace Club;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A new member, as the sign-up form sends it. The rules, with these messages:
 * - name: "A member needs a name." when blank; at most 100 characters: "A name has at most 100 characters.";
 * - email: "A member needs an email address." when blank; a real address: "<value> is not an email address."
 *   (the value in quotes, as Symfony writes {{ value }});
 * - password: at least 15 characters (chapter 61): "A password needs at least 15 characters.";
 * - role: reader or staff: "The role is reader or staff."
 */
final class NewMember
{
    #[Assert\NotBlank(message: 'A member needs a name.')]
    #[Assert\Length(max: 100, maxMessage: 'A name has at most {{ limit }} characters.')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'A member needs an email address.')]
    #[Assert\Email(message: '{{ value }} is not an email address.')]
    public ?string $email = null;

    #[Assert\Length(min: 15, minMessage: 'A password needs at least {{ limit }} characters.')]
    public ?string $password = null;

    #[Assert\Choice(choices: ['reader', 'staff'], message: 'The role is reader or staff.')]
    public ?string $role = 'reader';
}
