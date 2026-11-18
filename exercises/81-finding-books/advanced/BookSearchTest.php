<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'BookSearch.php');

final class BookSearchTest extends TestCase
{
    public function testAnEmptyAddressIsThePlainList(): void
    {
        $search = BookSearch::fromQuery([]);

        self::assertSame(['', false, Sort::Title, 1], [$search->q, $search->available, $search->sort, $search->page]);
        self::assertSame('?page=2', $search->link(2));
    }

    public function testTheAddressIsRead(): void
    {
        $search = BookSearch::fromQuery(['q' => '  Octavia Butler ', 'available' => '1', 'sort' => 'newest', 'page' => '12']);

        self::assertSame(['Octavia Butler', true, Sort::Newest, 12], [$search->q, $search->available, $search->sort, $search->page]);
        self::assertSame('?q=Octavia+Butler&available=1&sort=newest&page=13', $search->link(13));
    }

    public function testEmptyValuesAreTheDefaults(): void
    {
        $search = BookSearch::fromQuery(['q' => '', 'available' => '', 'sort' => '']);

        self::assertSame([false, Sort::Title, 1], [$search->available, $search->sort, $search->page]);
        self::assertSame('?page=1', $search->link(1));
    }

    public function testALongSearchIsCutInCharacters(): void
    {
        $search = BookSearch::fromQuery(['q' => str_repeat('é', 150)]);

        self::assertSame(str_repeat('é', BookSearch::MAX), $search->q);
    }

    /** @return iterable<string, array{array<string, mixed>, string}> the query, and the field in the message */
    public static function mistakes(): iterable
    {
        yield 'an unknown order' => [['sort' => 'price'], 'sort'];
        yield 'an order in capitals' => [['sort' => 'NEWEST'], 'sort'];
        yield 'page 0' => [['page' => '0'], 'page'];
        yield 'a negative page' => [['page' => '-1'], 'page'];
        yield 'a page with a sign' => [['page' => '+2'], 'page'];
        yield 'a page with a point' => [['page' => '2.0'], 'page'];
        yield 'a page with a space' => [['page' => ' 2'], 'page'];
        yield 'an empty page' => [['page' => ''], 'page'];
        yield 'a page too large for a number' => [['page' => '99999999999999999999'], 'page'];
        yield 'a page as a list' => [['page' => ['1']], 'page'];
        yield 'available as a word' => [['available' => 'yes'], 'available'];
    }

    /** @param array<string, mixed> $query */
    #[DataProvider('mistakes')]
    public function testAWrongValueNamesItsField(array $query, string $field): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/^$field\\b/");

        BookSearch::fromQuery($query);
    }
}
