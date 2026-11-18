<?php

declare(strict_types=1);

// An email with Symfony Mailer (chapter 82). The DSN says where it goes: null://null drops it, as in
// tests; smtp://mailpit:1025 hands it to Mailpit; sendmail://default to the server's own mail program.
// Every email has a text part; the HTML part is optional, and mail programs may not show it.

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

require __DIR__ . '/vendor/autoload.php';

$email = new Email()
    ->from(new Address('shelf@example.org', 'Shelf'))
    ->to('ana@example.org')
    ->subject('Confirm your email for Shelf')
    ->text("Hello Ana,\n\nplease confirm that this address is yours:\nhttps://shelf.example/confirm?…\n")
    ->html('<p>Hello Ana,</p><p><a href="https://shelf.example/confirm?…">Confirm my address</a></p>');

foreach (['From', 'To', 'Subject'] as $name) {
    echo $email->getHeaders()->get($name)?->toString(), "\n";
}
echo "\n", $email->getTextBody();

$transport = Transport::fromDsn('null://null');
$sent = $transport->send($email);
echo "\nSent through ", $transport, ' to ', implode(', ', array_map(fn(Address $a): string => $a->getAddress(), $sent?->getEnvelope()->getRecipients() ?? [])), "\n";
new Mailer($transport)->send($email); // the same, as Symfony's MailerInterface does it
