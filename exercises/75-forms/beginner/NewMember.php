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
    // TODO: the rules
    public ?string $name = null;

    public ?string $email = null;

    #[Assert\Length(min: 8)]
    public ?string $password = null;

    public ?string $role = 'reader';
}
