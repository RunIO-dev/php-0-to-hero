<?php

declare(strict_types=1);

namespace Shelf\Command;

use Shelf\Accounts;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * php bin/console shelf:password Amira: sets a member's password (chapter 61), typed without being
 * shown, or the first line of a file: < amira-password.txt. Only its hash is stored.
 */
#[AsCommand('shelf:password', "Sets a member's password")]
final readonly class PasswordCommand
{
    public function __construct(private Accounts $accounts) {}

    public function __invoke(SymfonyStyle $io, InputInterface $input, #[Argument('The member')] string $name): int
    {
        // Typed, without showing it; or, with < file, the first line of the file. Chapter 77: read from
        // the input's stream, not STDIN itself, so a test can give the command its password.
        $stream = ($input instanceof StreamableInputInterface ? $input->getStream() : null) ?? STDIN;
        $password = stream_isatty($stream) ? $io->askHidden("New password for $name") : fgets($stream);
        $this->accounts->setPassword($name, rtrim(is_string($password) ? $password : '', "\r\n"));
        $io->writeln("Saved a new password for $name");

        return Command::SUCCESS;
    }
}
