<?php

declare(strict_types=1);

namespace Shelf\Tests\Command;

use Shelf\Accounts;
use Shelf\BookRepository;
use Shelf\LoanRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Shelf's commands (chapter 74), run as bin/console runs them, against the test database (chapter 77).
 * Each test starts with the tables empty, and its changes are rolled back at its end.
 */
final class CommandsTest extends KernelTestCase
{
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
