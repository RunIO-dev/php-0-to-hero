<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BranchTest extends TestCase
{
    public function testEachBranchHasItsOwnBooks(): void
    {
        require_once subject(__DIR__, 'branch.php');

        $north = new Branch('North');
        $south = new Branch('South');
        $north->add('Kindred');
        $north->add('Kitchen');
        $south->add('Sula');

        self::assertSame(['Kindred', 'Kitchen'], $north->titles());
        self::assertSame(['Sula'], $south->titles());
    }

    public function testANewBranchIsEmpty(): void
    {
        require_once subject(__DIR__, 'branch.php');

        self::assertSame([], (new Branch('East'))->titles());
    }

    public function testNoStaticProperties(): void
    {
        require_once subject(__DIR__, 'branch.php');

        self::assertSame([], (new ReflectionClass(Branch::class))->getStaticProperties());
    }
}
