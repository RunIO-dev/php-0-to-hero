<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'name.php');

final class NameTest extends TestCase
{
    /** @return iterable<string, array{string, bool}> */
    public static function names(): iterable
    {
        yield 'two words' => ['Ana Lima', true];
        yield 'an apostrophe and a diaeresis' => ["Zoë O'Neil", true];
        yield 'Arabic letters' => ['أمل', true];
        yield 'with a digit' => ['Reader 7', true];
        yield 'spaces around it' => ['  Ana  ', true];
        yield 'two letters' => ['Al', true];
        yield 'forty letters' => [str_repeat('a', 40), true];
        yield 'one letter' => ['A', false];
        yield 'forty-one letters' => [str_repeat('a', 41), false];
        yield 'a hyphen first' => ['-Ana', false];
        yield 'a tag' => ['<b>Ana</b>', false];
        yield 'an at sign' => ['ana@home', false];
        yield 'a line break' => ["Ana\nLima", false];
        yield 'nothing' => ['', false];
    }

    #[DataProvider('names')]
    public function testANameIsCheckedByTheRules(string $name, bool $valid): void
    {
        self::assertSame($valid, validName($name));
    }
}
