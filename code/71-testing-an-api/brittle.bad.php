<?php

declare(strict_types=1);

// ✗ The whole answer, character by character: a space, the order of two fields, or a new field
// breaks the test, though no program would notice. And it says nothing about what matters.

final class BrittleTest extends PHPUnit\Framework\TestCase
{
    public function testOneBook(): void
    {
        self::assertSame("{\"id\":2,\"title\":\"Kindred\",\"author\":\"Octavia E. Butler\",\"year\":1979}\n", handle('GET', '/book', ['id' => '2'])['body']);
    }
}
