<?php

declare(strict_types=1);

namespace Shelf\Web;

use Shelf\Hold;
use Shelf\Loan;
use Shelf\Reminder;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The reminder emails (chapter 85), one Twig template per Reminder, as AccountMail's (chapter 82).
 * They are made in the worker, where there is no request: the link's address comes from
 * framework.router.default_uri (SHELF_URL).
 */
final readonly class ReminderMail
{
    public function __construct(private MailerInterface $mailer, private UrlGeneratorInterface $urls) {}

    public function send(Loan|Hold $about, Reminder $kind): void
    {
        $day = $about instanceof Loan ? $about->due : $about->readyUntil ?? throw new \LogicException('No copy is set aside');
        $this->mailer->send(new TemplatedEmail()
            ->to(new Address((string) $about->member->email))
            ->subject($kind->subject($about->book->title, $day))
            ->textTemplate("email/{$kind->value}.txt.twig")
            ->htmlTemplate("email/{$kind->value}.html.twig")
            ->context([
                'name' => $about->member->name,
                'title' => $about->book->title,
                'day' => $day->format('l, j F Y'),
                'link' => $this->urls->generate('loans', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]));
    }
}
