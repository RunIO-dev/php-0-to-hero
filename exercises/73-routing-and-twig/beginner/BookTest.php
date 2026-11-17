<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class BookTest extends TestCase
{
    private const array KINDRED = ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979, 'isbn' => '9780807083697', 'copiesLeft' => 2];

    public function testTheCardShowsTheBook(): void
    {
        $html = $this->render(self::KINDRED);

        self::assertStringContainsString('<h2>Kindred</h2>', $html);
        self::assertStringContainsString('<p>Octavia E. Butler, 1979</p>', $html);
        self::assertStringContainsString('<p>ISBN 9780807083697</p>', $html);
        self::assertStringContainsString('Available', $html);
        self::assertStringNotContainsString('on loan', $html);
    }

    public function testNoIsbnNoLine(): void
    {
        self::assertStringNotContainsString('ISBN', $this->render(['isbn' => null] + self::KINDRED));
    }

    public function testNoCopyLeftIsOnLoan(): void
    {
        $html = $this->render(['copiesLeft' => 0] + self::KINDRED);

        self::assertStringContainsString('All copies on loan', $html);
        self::assertStringNotContainsString('Available', $html);
    }

    public function testEveryValueIsEscaped(): void
    {
        $html = $this->render(['title' => '<script>steal()</script>', 'author' => 'Ana & "Kenji"'] + self::KINDRED);

        self::assertStringContainsString('<h2>&lt;script&gt;steal()&lt;/script&gt;</h2>', $html);
        self::assertStringContainsString('Ana &amp; &quot;Kenji&quot;', $html);
        self::assertStringNotContainsString('<script>', $html);
    }

    /** @param array<string, mixed> $book */
    private function render(array $book): string
    {
        $twig = new Environment(new FilesystemLoader(dirname(subject(__DIR__, 'book.html.twig'))), ['strict_variables' => true]);

        return $twig->render('book.html.twig', ['book' => $book]);
    }
}
