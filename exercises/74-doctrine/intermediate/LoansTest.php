<?php

declare(strict_types=1);

use Desk\Book;
use Desk\Loan;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

require_once subject(__DIR__, 'loans.php');
require_once dirname(subject(__DIR__, 'loans.php')) . '/entities/Book.php';
require_once dirname(subject(__DIR__, 'loans.php')) . '/entities/Loan.php';

final class LoansTest extends TestCase
{
    private EntityManager $entities;

    /** @var list<string> */
    private array $queries = [];

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfig([], isDevMode: true);
        $config->enableNativeLazyObjects(true);
        $queries = &$this->queries;
        $config->setMiddlewares([new Middleware(new class ($queries) extends AbstractLogger {
            /** @param list<string> $queries */
            public function __construct(private array &$queries) {}

            public function log($level, string|Stringable $message, array $context = []): void
            {
                if (isset($context['sql']) && is_string($context['sql']) && str_starts_with($context['sql'], 'SELECT')) {
                    $this->queries[] = $context['sql'];
                }
            }
        })]);
        $this->entities = new EntityManager(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config), $config);
        new SchemaTool($this->entities)->createSchema([$this->entities->getClassMetadata(Book::class), $this->entities->getClassMetadata(Loan::class)]);

        $books = [];
        foreach (['Kindred', 'Sula', 'Kitchen', 'Beloved'] as $title) {
            $this->entities->persist($books[$title] = new Book($title));
        }
        foreach ([['Kindred', 'Amira', '2026-11-10'], ['Sula', 'Amira', '2026-11-30'], ['Sula', 'Jonas', '2026-11-01'], ['Kitchen', 'Jonas', '2026-11-20']] as [$title, $member, $due]) {
            $this->entities->persist(new Loan($books[$title], $member, new DateTimeImmutable($due)));
        }
        $this->entities->flush();
        $this->entities->clear();
        $this->queries = [];
    }

    public function testOverdueLoansTheLongestFirst(): void
    {
        $loans = overdue($this->entities, new DateTimeImmutable('2026-11-15'));

        self::assertSame([['Sula', 'Jonas'], ['Kindred', 'Amira']], array_map(static fn(Loan $l): array => [$l->book->title, $l->member], $loans));
        self::assertCount(1, $this->queries, 'One query, with the books in it');
    }

    public function testDueTodayIsNotOverdue(): void
    {
        self::assertCount(1, overdue($this->entities, new DateTimeImmutable('2026-11-10')));
    }

    public function testAMembersTitlesTheSoonestFirst(): void
    {
        self::assertSame(['Kindred', 'Sula'], titlesOf($this->entities, 'Amira'));
        self::assertSame([], titlesOf($this->entities, 'Ana'));
    }

    public function testANameIsAValueNotDql(): void
    {
        self::assertSame([], titlesOf($this->entities, "Amira' OR l.member <> '"));
    }

    public function testTheBusiestBooks(): void
    {
        self::assertSame(['Sula' => 2, 'Kindred' => 1, 'Kitchen' => 1], busiest($this->entities));
    }
}
