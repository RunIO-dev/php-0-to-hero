<?php

declare(strict_types=1);

use Catalogue\Book;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'BookFinder.php');
require_once dirname(subject(__DIR__, 'BookFinder.php')) . '/entities/Book.php';

final class BookFinderTest extends TestCase
{
    private BookFinder $finder;

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfig([], isDevMode: true);
        $config->enableNativeLazyObjects(true);
        $entities = new EntityManager(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config), $config);
        new SchemaTool($entities)->createSchema([$entities->getClassMetadata(Book::class)]);
        $books = [
            ['Sula', 'Toni Morrison', 1], ['Kindred', 'Octavia E. Butler', 0], ['Beloved', 'Toni Morrison', 2],
            ['Parable of the Sower', 'Octavia E. Butler', 1], ['Kitchen', 'Banana Yoshimoto', 3],
            ['Things Fall Apart', 'Chinua Achebe', 1], ['Wild Seed', 'Octavia E. Butler', 2], ['100% Tea', 'Ana 100_% Lima', 1],
        ];
        foreach ($books as [$title, $author, $copies]) {
            $entities->persist(new Book($title, $author, $copies));
        }
        $entities->flush();
        $this->finder = new BookFinder($entities);
    }

    public function testPagesAreSortedByTitle(): void
    {
        self::assertSame(['100% Tea', 'Beloved', 'Kindred'], $this->titles('', false, 1, 3));
        self::assertSame(['Kitchen', 'Parable of the Sower', 'Sula'], $this->titles('', false, 2, 3));
        self::assertSame(['Things Fall Apart', 'Wild Seed'], $this->titles('', false, 3, 3));
        self::assertSame([], $this->titles('', false, 4, 3));
    }

    public function testFilteredByPartOfTheAuthorInAnyCase(): void
    {
        self::assertSame(['Kindred', 'Parable of the Sower', 'Wild Seed'], $this->titles('BUTLER', false, 1, 10));
        self::assertSame(3, $this->finder->count('butler', false));
    }

    public function testOnlyBooksOnTheShelf(): void
    {
        self::assertSame(['Parable of the Sower', 'Wild Seed'], $this->titles('butler', true, 1, 10));
        self::assertSame(7, $this->finder->count('', true));
    }

    public function testWildcardsAreLetters(): void
    {
        self::assertSame(['100% Tea'], $this->titles('%', false, 1, 10), 'Only the author with a % in the name');
        self::assertSame(['100% Tea'], $this->titles('100_%', false, 1, 10));
        self::assertSame(0, $this->finder->count('_a', false));
    }

    public function testPagesCountFromOne(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->finder->page('', false, 0, 10);
    }

    /** @return list<string> */
    private function titles(string $author, bool $availableOnly, int $page, int $perPage): array
    {
        return array_map(static fn(Book $book): string => $book->title, $this->finder->page($author, $availableOnly, $page, $perPage));
    }
}
