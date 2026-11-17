<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';
require_once subject(__DIR__, 'lend.php');

final class LendTest extends TestCase
{
    public function testALoanTakesACopy(): void
    {
        $db = testLibrary();

        self::assertSame(4, lend($db, 'Kitchen', 'Jonas', '2026-04-01'));
        self::assertSame('Kindred 0, Sula 2, Kitchen 0, Beloved 3; loans 1, 2, 3, 4', stateOf($db));
    }

    /** @return iterable<string, array{string, string, class-string<Throwable>, string}> */
    public static function refused(): iterable
    {
        yield 'no copy left' => ['Kindred', 'Jonas', DomainException::class, 'All copies of "Kindred" are on loan'];
        yield 'three books already' => ['Kitchen', 'Amira', DomainException::class, 'Amira has 3 books already'];
        yield 'an unknown book' => ['Dune', 'Jonas', OutOfBoundsException::class, ''];
        yield 'an unknown member' => ['Kitchen', 'Kenji', OutOfBoundsException::class, ''];
    }

    /** @param class-string<Throwable> $exception */
    #[DataProvider('refused')]
    public function testARefusalChangesNothing(string $title, string $member, string $exception, string $message): void
    {
        $db = testLibrary();

        try {
            lend($db, $title, $member, '2026-04-01');
            self::fail("Lending $title to $member must be refused");
        } catch (Throwable $e) {
            self::assertInstanceOf($exception, $e);
            self::assertStringContainsString($message, $e->getMessage());
        }
        self::assertSame('Kindred 0, Sula 2, Kitchen 1, Beloved 3; loans 1, 2, 3', stateOf($db));
        self::assertFalse($db->inTransaction());
    }

    public function testTheLastCopyGoesOnce(): void
    {
        $db = testLibrary();
        lend($db, 'Kitchen', 'Jonas', '2026-04-01');

        $this->expectException(DomainException::class);

        lend($db, 'Kitchen', 'Jonas', '2026-04-01');
    }
}
