<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class AvailabilityTest extends TestCase
{
    private function badge(int $copies): Crawler
    {
        $template = subject(__DIR__, 'availability.html.twig');
        $twig = new Environment(new FilesystemLoader(dirname($template)), ['strict_variables' => true]);

        return new Crawler($twig->render(basename($template), ['copies' => $copies]));
    }

    /** @return iterable<string, array{int, string, string}> */
    public static function copies(): iterable
    {
        yield 'none' => [0, 'All copies on loan', 'warn'];
        yield 'one' => [1, '1 copy on the shelf', 'ok'];
        yield 'two' => [2, '2 copies on the shelf', 'ok'];
        yield 'many' => [12, '12 copies on the shelf', 'ok'];
    }

    #[DataProvider('copies')]
    public function testTheBadgeSaysItInWordsAndColour(int $copies, string $text, string $level): void
    {
        $badge = $this->badge($copies)->filter('span.badge');

        self::assertCount(1, $badge, 'One badge');
        self::assertSame($text, trim($badge->text()));
        self::assertSame("badge $level", $badge->attr('class'));
    }
}
