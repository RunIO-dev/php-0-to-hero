<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class LoansTest extends TestCase
{
    public function testThePageIsInsideTheLayout(): void
    {
        $html = $this->render('Amira', []);

        self::assertStringStartsWith('<!doctype html>', $html);
        self::assertStringContainsString('<title>Loans of Amira · North Library</title>', $html);
        self::assertStringContainsString('<h1>Loans of Amira</h1>', $html);
    }

    public function testEachLoanIsAnItem(): void
    {
        $html = $this->render('Jonas', [['title' => 'Kindred', 'due' => '2026-12-01'], ['title' => 'Sula', 'due' => '2026-12-08']]);

        self::assertStringContainsString('<li>Kindred, due 2026-12-01</li>', $html);
        self::assertStringContainsString('<li>Sula, due 2026-12-08</li>', $html);
        self::assertStringContainsString('2 loans', $html);
    }

    public function testOneLoanIsSingular(): void
    {
        self::assertStringContainsString('1 loan<', $this->render('Ana', [['title' => 'Kitchen', 'due' => '2026-11-30']]));
    }

    public function testNoLoansNoList(): void
    {
        $html = $this->render('Kenji', []);

        self::assertStringContainsString('<p>No loans.</p>', $html);
        self::assertStringNotContainsString('<ul>', $html);
        self::assertStringContainsString('0 loans', $html);
    }

    public function testValuesAreEscaped(): void
    {
        $html = $this->render('<b>Ana</b>', [['title' => 'A & B', 'due' => '2026-11-30']]);

        self::assertStringContainsString('Loans of &lt;b&gt;Ana&lt;/b&gt;', $html);
        self::assertStringContainsString('A &amp; B', $html);
    }

    /** @param list<array{title: string, due: string}> $loans */
    private function render(string $name, array $loans): string
    {
        $twig = new Environment(new FilesystemLoader(dirname(subject(__DIR__, 'loans.html.twig'))), ['strict_variables' => true]);

        return $twig->render('loans.html.twig', ['name' => $name, 'loans' => $loans]);
    }
}
