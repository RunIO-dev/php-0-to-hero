<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LabelTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = subject(__DIR__, 'label.php');
    }

    public function testPrintsTheLabel(): void
    {
        self::assertSame("Kindred, by Octavia E. Butler (1979)\n", output_of($this->file));
    }

    public function testUsesTheVariables(): void
    {
        $text = implode('', strings_in($this->file));
        foreach (['Kindred', 'Octavia'] as $value) {
            self::assertSame(1, substr_count($text, $value), "Write '$value' only once, in its variable.");
        }
        $years = array_filter(
            \PhpToken::tokenize((string) file_get_contents($this->file)),
            static fn(\PhpToken $token): bool => $token->is(T_LNUMBER) && $token->text === '1979',
        );
        self::assertCount(1, $years, 'Write 1979 only once, in $year.');
    }
}
