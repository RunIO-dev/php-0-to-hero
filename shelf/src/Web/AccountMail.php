<?php

declare(strict_types=1);

namespace Shelf\Web;

use Shelf\Member;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * The emails about an account (chapter 82). Each is a Twig template with a text and an HTML part, and
 * a signed link from AccountLinks. Symfony Mailer sends it through MAILER_DSN; the sender is set
 * once, in config/packages/mailer.yaml.
 */
final readonly class AccountMail
{
    public function __construct(private MailerInterface $mailer, private AccountLinks $links) {}

    /** After sign-up, and after a member enters a new address: the link proves the address is theirs. */
    public function confirm(Member $member, string $email): void
    {
        $this->send($email, 'Confirm your email for Shelf', 'confirm', ['name' => $member->name, 'link' => $this->links->confirm($member, $email)]);
    }

    /** Someone signed up with an address that has an account: its owner hears of it, the page says nothing. */
    public function alreadyMember(Member $member): void
    {
        $this->send((string) $member->email, 'You already have an account at Shelf', 'already', ['name' => $member->name, 'link' => $this->links->reset($member)]);
    }

    public function reset(Member $member): void
    {
        $this->send((string) $member->email, 'A new password for Shelf', 'reset', ['name' => $member->name, 'link' => $this->links->reset($member)]);
    }

    /** @param array<string, string> $context */
    private function send(string $to, string $subject, string $template, array $context): void
    {
        $this->mailer->send(new TemplatedEmail()
            ->to(new Address($to))
            ->subject($subject)
            ->textTemplate("email/$template.txt.twig")
            ->htmlTemplate("email/$template.html.twig")
            ->context($context));
    }
}
