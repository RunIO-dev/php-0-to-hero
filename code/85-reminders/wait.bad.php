<?php

declare(strict_types=1);

// ✗ The page sends its email itself. While the mail server is slow, the member waits for the page;
// while it is down, the request fails, and the email is gone. The page should only queue it.

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

function signUp(MailerInterface $mailer, string $address): void
{
    // ... the account is stored ...
    $mailer->send(new Email()->to($address)->subject('Confirm your email for Shelf')->text('...')); // SMTP, now
}
