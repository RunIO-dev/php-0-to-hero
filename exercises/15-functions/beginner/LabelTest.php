<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LabelTest extends TestCase
{
    public function testReturnsTheLabel(): void
    {
        require_once subject(__DIR__, 'label.php');

        self::assertSame('Shelf 3: Science fiction', shelfLabel(3, 'Science fiction'));
        self::assertSame('Shelf 12: Poetry', shelfLabel(12, 'Poetry'));
    }

    public function testPrintsNothing(): void
    {
        require_once subject(__DIR__, 'label.php');

        $this->expectOutputString('');
        shelfLabel(1, 'History');
    }
}
