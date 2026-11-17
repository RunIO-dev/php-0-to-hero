<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CardTest extends TestCase
{
    /** @param array<string, mixed> $data */
    private function render(array $data): string
    {
        ob_start();
        $template = require subject(__DIR__, 'card.php');
        $loaded = (string) ob_get_clean();
        self::assertSame('', $loaded, 'Loading the file printed something');
        ob_start();
        try {
            $template(...$data);
        } finally {
            $html = (string) ob_get_clean();
        }

        return trim($html);
    }

    public function testTheCardShowsTheValuesEscaped(): void
    {
        self::assertSame(
            '<div class="card"><h2>Amira &lt;b&gt;</h2><p>Reader &amp; helper · 2 loans</p></div>',
            $this->render(['name' => 'Amira <b>', 'role' => 'Reader & helper', 'loans' => 2]),
        );
    }

    public function testTheLoansMustBeANumber(): void
    {
        $this->expectException(TypeError::class);

        $this->render(['name' => 'Amira', 'role' => 'Reader', 'loans' => 'two']);
    }
}
