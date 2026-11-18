<?php

declare(strict_types=1);

// Filters from attributes (chapter 80): a PHP method with #[AsTwigFilter] becomes a Twig filter.
// What a page shows is worked out in PHP, where it can be tested; the template only uses it.
// In Symfony, the class is found on its own; here, AttributeExtension adds it by hand.

use Twig\Attribute\AsTwigFilter;
use Twig\Environment;
use Twig\Extension\AttributeExtension;
use Twig\Loader\ArrayLoader;

require __DIR__ . '/vendor/autoload.php';

final class Covers
{
    /** One of six colours, always the same for the same title. */
    #[AsTwigFilter('tone')]
    public static function tone(string $title): int
    {
        return crc32($title) % 6;
    }

    /** The first letter, in capitals, also for a title that starts with a lowercase or an accented letter. */
    #[AsTwigFilter('initial')]
    public static function initial(string $title): string
    {
        return mb_strtoupper(mb_substr($title, 0, 1));
    }
}

$twig = new Environment(new ArrayLoader([
    'cover' => '<span class="cover tone-{{ title|tone }}">{{ title|initial }}</span>' . "\n",
]));
$twig->addExtension(new AttributeExtension(Covers::class));

foreach (['Kindred', 'Sula', 'ébène', '<b>Beloved</b>'] as $title) {
    echo $twig->render('cover', ['title' => $title]);
}
