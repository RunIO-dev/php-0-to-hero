<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LabelsTest extends TestCase
{
    public function testLabelsEveryTitle(): void
    {
        require_once subject(__DIR__, 'labels.php');

        self::assertSame(['[KINDRED]', '[KITCHEN]'], labels(['Kindred', 'Kitchen']));
        self::assertSame(['[ÜBER DIE LIEBE]'], labels(['Über die Liebe']));
        self::assertSame([], labels([]));
    }

    public function testUsesArrayMapAndFn(): void
    {
        $tokens = PhpToken::tokenize((string) file_get_contents(subject(__DIR__, 'labels.php')));
        $has = static fn(int $kind, ?string $text = null): bool => array_filter(
            $tokens,
            static fn(PhpToken $t): bool => $t->is($kind) && ($text === null || strtolower($t->text) === $text),
        ) !== [];

        self::assertTrue($has(T_STRING, 'array_map'), 'Use array_map.');
        self::assertTrue($has(T_FN), 'Use an arrow function: fn(...) => ...');
        self::assertFalse($has(T_FOREACH), 'No foreach this time.');
    }
}
