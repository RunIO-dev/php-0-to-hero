<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShelfNameTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function genres(): array
    {
        return [
            'sf' => ["'sf'", 'Science fiction'],
            'crime' => ["'crime'", 'Crime'],
            'poetry' => ["'poetry'", 'Poetry'],
            'kids' => ["'kids'", 'Young readers'],
            'teens' => ["'teens'", 'Young readers'],
            'anything else' => ["'cooking'", 'General'],
        ];
    }

    #[DataProvider('genres')]
    public function testFindsTheShelf(string $genre, string $expected): void
    {
        self::assertSame("$expected\n", output_with(subject(__DIR__, 'shelf-name.php'), ['genre' => $genre]));
    }

    public function testUsesOneMatchInsteadOfIf(): void
    {
        $tokens = PhpToken::tokenize((string) file_get_contents(subject(__DIR__, 'shelf-name.php')));
        $kinds = array_map(static fn(PhpToken $token): int => $token->id, $tokens);
        self::assertContains(T_MATCH, $kinds, 'Use a match expression.');
        self::assertNotContains(T_IF, $kinds, 'Replace the if and elseif chain with the match.');
    }
}
