<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ThreeBooksTest extends TestCase
{
    public function testTheOutputStaysTheSame(): void
    {
        self::assertSame(
            "Things Fall Apart by Chinua Achebe (1958)\nKindred by Octavia E. Butler (1979)\nKitchen by Banana Yoshimoto (1988)\n",
            output_of(subject(__DIR__, 'three-books.php')),
        );
    }

    public function testBookLineReturnsOneLine(): void
    {
        ob_start();
        try {
            require_once subject(__DIR__, 'three-books.php');
        } finally {
            ob_end_clean();
        }

        self::assertTrue(function_exists('bookLine'), 'Write a function named bookLine.');
        self::assertSame('Beloved by Toni Morrison (1987)', bookLine(['title' => 'Beloved', 'author' => 'Toni Morrison', 'year' => 1987]));
    }

    public function testTheLineIsWrittenOnce(): void
    {
        $code = (string) file_get_contents(subject(__DIR__, 'three-books.php'));
        $code = (string) preg_replace('~^//.*$~m', '', $code); // the instructions don't count

        self::assertSame(1, substr_count($code, ' by '), "Write ' by ' once, inside bookLine().");
    }
}
