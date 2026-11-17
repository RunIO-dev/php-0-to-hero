<?php

declare(strict_types=1);

namespace Shelf\Command;

use Shelf\Accounts;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * php bin/console shelf:password Amira: sets a member's password (chapter 61), typed without being
 * shown, or the first line of a file: < amira-password.txt. Only its hash is stored.
 */
#[AsCommand('shelf:password', "Sets a member's password")]
final readonly class PasswordCommand
{
    public function __construct(private Accounts $accounts) {}

    public function __invoke(SymfonyStyle $io, #[Argument('The member')] string $name): int
    {
        // Typed, without showing it; or, with < file, the first line of the file.
        $password = stream_isatty(STDIN) ? $io->askHidden("New password for $name") : fgets(STDIN);
        $this->accounts->setPassword($name, rtrim(is_string($password) ? $password : '', "\r\n"));
        $io->writeln("Saved a new password for $name");

        return Command::SUCCESS;
    }
}
