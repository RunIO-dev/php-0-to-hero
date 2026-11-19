<?php

declare(strict_types=1);

namespace Shelf\Tests\Command;

use Shelf\Accounts;
use DateTimeImmutable;
use Shelf\BookRepository;
use Shelf\Library;
use Shelf\MemberRepository;
use Shelf\LoanRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Shelf's commands (chapter 74), run as bin/console runs them, against the test database (chapter 77).
 * Each test starts with the tables empty, and its changes are rolled back at its end.
 */
final class CommandsTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    public function testImportFillsAnEmptyDatabase(): void
    {
        $import = self::command('shelf:import');
        $import->execute([]);

        $import->assertCommandIsSuccessful();
        self::assertSame("Imported 4 books and 4 members\n", $import->getDisplay());
        self::assertSame(1979, self::service(BookRepository::class)->find('Kindred')?->year);
    }

    public function testImportNeverImportsTwice(): void
    {
        self::command('shelf:import')->execute([]);

        $this->expectExceptionMessage('The database already has books; nothing was imported.');
        self::command('shelf:import')->execute([]);
    }

    public function testPasswordReadsTheFirstLine(): void
    {
        self::command('shelf:import')->execute([]);
        $password = self::command('shelf:password');
        $password->setInputs(['tea kettle under the lemon tree', 'a second line']);

        $password->execute(['name' => 'Amira']);

        self::assertSame("Saved a new password for Amira\n", $password->getDisplay(), 'No prompt, no password');
        self::assertNotNull(self::service(Accounts::class)->authenticate('Amira', 'tea kettle under the lemon tree'));
    }

    public function testLendTakesACopy(): void
    {
        self::command('shelf:import')->execute([]);
        $lend = self::command('shelf:lend');

        $lend->execute(['title' => 'kitchen', 'name' => 'Amira']);

        $lend->assertCommandIsSuccessful();
        self::assertStringStartsWith('Lent: Kitchen to Amira, due back on ', $lend->getDisplay());
        self::assertTrue(self::service(LoanRepository::class)->isOnLoan('Kitchen'));
        self::assertSame(0, self::service(BookRepository::class)->find('Kitchen')?->copies);
    }

    /** Chapter 84: a copy nobody collected in a week goes back on the shelf. */
    public function testHoldsPassesOnUncollectedCopies(): void
    {
        self::command('shelf:import')->execute([]);
        $library = self::service(Library::class);
        $loan = $library->lend('Kitchen', 'Amira', new DateTimeImmutable('2026-03-01'));
        $library->reserve('Kitchen', self::service(MemberRepository::class)->find('Jonas') ?? self::fail('No Jonas'), new DateTimeImmutable('2026-03-01'));
        $library->giveBack($loan->id(), new DateTimeImmutable('2026-03-02'));
        self::mockTime('2026-03-10 06:00');

        $holds = self::command('shelf:holds');
        $holds->execute([]);

        self::assertSame("- \"Kitchen\" waited for Jonas until 9 March: back on the shelf\n", $holds->getDisplay());
        self::assertSame(1, self::service(BookRepository::class)->find('Kitchen')?->copies);
        $holds->execute([]);
        self::assertSame("No reservation has expired.\n", $holds->getDisplay(), 'Once is enough');
    }

    /** Chapter 81: the catalogue may run after every deployment; the second run adds nothing. */
    public function testTheCatalogueAddsOnlyWhatIsNew(): void
    {
        $file = self::bootKernel()->getProjectDir() . '/catalogue.json';
        $first = self::command('shelf:catalogue');
        $first->execute(['file' => $file]);
        $again = self::command('shelf:catalogue');
        $again->execute(['file' => $file]);

        $first->assertCommandIsSuccessful();
        self::assertSame("New books added: 53. Already on the shelf: 0.\n", $first->getDisplay());
        self::assertSame("New books added: 0. Already on the shelf: 53.\n", $again->getDisplay());
        self::assertSame('Ursula K. Le Guin', self::service(BookRepository::class)->find('the dispossessed')?->author);
    }

    public function testATitleTwiceInTheFileIsAddedOnce(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'shelf');
        self::assertIsString($file);
        file_put_contents($file, '[{"title": "Sula", "author": "Toni Morrison", "year": 1973, "copies": 1},'
            . ' {"title": "SULA", "author": "Toni Morrison", "year": 1973, "copies": 2}]');
        $catalogue = self::command('shelf:catalogue');
        $catalogue->execute(['file' => $file]);
        unlink($file);

        self::assertSame("New books added: 1. Already on the shelf: 1.\n", $catalogue->getDisplay());
        self::assertSame(1, self::service(BookRepository::class)->find('Sula')?->copies);
    }

    /** Chapter 88: a reset deletes everything, so it runs only in a demo. */
    public function testDemoRefusesARealLibrary(): void
    {
        self::command('shelf:import')->execute([]);
        $demo = self::command('shelf:demo');

        self::assertSame(Command::FAILURE, $demo->execute([]));
        self::assertStringContainsString('Shelf is not a demo (SHELF_DEMO=0)', $demo->getDisplay());
        self::assertNotNull(self::service(BookRepository::class)->find('Kindred'), 'Nothing deleted');
    }

    /** Chapter 88: the launch checklist, on the tests' settings: much of it fails, and says what to do. */
    public function testLaunchNamesWhatIsMissing(): void
    {
        self::mockTime('2026-03-16 10:00');
        $launch = self::command('shelf:launch');
        $data = $_SERVER['SHELF_DATA'] ?? null; // from .env.test: no heartbeat, no backup left by another test
        self::assertIsString($data);
        new Filesystem()->remove(self::$kernel?->getProjectDir() . "/$data");

        self::assertSame(Command::FAILURE, $launch->execute([]));
        $lines = explode("\n", rtrim($launch->getDisplay()));
        self::assertSame(' ✗ APP_ENV is prod. Set APP_ENV=prod on the server.', $lines[0]);
        self::assertSame(' ✓ The database has every migration', $lines[5]);
        self::assertSame(' ✗ A member of staff can log in. Set a password for one: php bin/console shelf:password <name>', $lines[6]);
        self::assertSame(' ✓ The database answers', $lines[7]);
        self::assertSame('Not ready: 8 of 10 checks failed.', end($lines));
    }

    private static function command(string $name): CommandTester
    {
        return new CommandTester(new Application(self::$kernel ?? self::bootKernel())->find($name));
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     */
    private static function service(string $id): object
    {
        $service = static::getContainer()->get($id);
        self::assertInstanceOf($id, $service);

        return $service;
    }
}
