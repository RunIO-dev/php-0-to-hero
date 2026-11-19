<?php

declare(strict_types=1);

namespace Shelf\Command;

use Doctrine\Migrations\DependencyFactory;
use Psr\Clock\ClockInterface;
use Shelf\Demo;
use Shelf\Health;
use Shelf\Member;
use Shelf\MemberRepository;
use Shelf\Role;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * php bin/console shelf:launch (chapter 88): the part of the launch checklist a program can check, on
 * the server, with the server's settings. Each line is a yes or a no, and a no says what to do. The
 * command fails while any answer is no, so a deployment can stop on it.
 */
#[AsCommand('shelf:launch', 'Checks that this server is ready to put Shelf online')]
final readonly class LaunchCommand
{
    public function __construct(
        private Health $health,
        private MemberRepository $members,
        private Demo $demo,
        private ClockInterface $clock,
        #[Autowire(service: 'doctrine.migrations.dependency_factory')]
        private DependencyFactory $migrations,
        #[Autowire('%kernel.environment%')]
        private string $environment,
        #[Autowire('%kernel.debug%')]
        private bool $debug,
        #[Autowire('%kernel.secret%')]
        private string $secret,
        #[Autowire('%env(SHELF_URL)%')]
        private string $url,
        #[Autowire('%env(MAILER_DSN)%')]
        private string $mailer,
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        $health = $this->health->check($this->clock->now());
        $checks = [
            ['APP_ENV is prod', $this->environment === 'prod', 'Set APP_ENV=prod on the server.'],
            ['Debugging is off', !$this->debug, 'Set APP_DEBUG=0: error pages would show the code.'],
            ['The secret is long and random', strlen($this->secret) >= 32, 'Set APP_SECRET to 32 random bytes or more: openssl rand -hex 32'],
            ['The address is https://', str_starts_with($this->url, 'https://'), 'Set SHELF_URL to the https:// address of the site, for the links in emails.'],
            $this->demo->on
                ? ['Email is not needed in the demo', true, '']
                : ['Email is sent', !str_starts_with($this->mailer, 'null://'), 'Set MAILER_DSN to the server\'s mail, or no member gets a confirmation or a reminder.'],
            ['The database has every migration', $this->migrations->getMigrationStatusCalculator()->getNewMigrations()->count() === 0, 'Run: php bin/console doctrine:migrations:migrate'],
            ['A member of staff can log in', $this->staffCanLogIn(), 'Set a password for one: php bin/console shelf:password <name>'],
            ['The database answers', $health['database'], 'Check DATABASE_URL, and that the user who runs PHP may write the file and its folder.'],
            ['The worker ran in the last ' . Health::WORKER_LATE, $health['worker'], 'Start it, and keep it running: php bin/console messenger:consume async scheduler_shelf'],
            ['A backup is less than ' . Health::BACKUP_LATE . ' old', $health['backup'], 'Run php bin/console shelf:backup once; then the worker makes one every night.'],
        ];
        $failed = 0;
        foreach ($checks as [$what, $ok, $fix]) {
            $io->writeln($ok ? " ✓ $what" : " ✗ $what. $fix");
            $failed += $ok ? 0 : 1;
        }
        $io->newLine();
        $io->writeln($failed === 0 ? 'Ready to launch.' : "Not ready: $failed of " . count($checks) . ' checks failed.');

        return $failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function staffCanLogIn(): bool
    {
        return array_any(
            $this->members->all(),
            fn(Member $member): bool => $member->role === Role::Staff && !$member->blocked && $this->members->passwordHash($member->name) !== null,
        );
    }
}
