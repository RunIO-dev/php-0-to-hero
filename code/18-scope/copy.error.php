<?php

declare(strict_types=1);

/**
 * @param list<string> $titles
 * @return list<string>
 */
function withTitle(array $titles, string $title): array
{
    // $titles is a copy: adding to it changes nothing outside.
    $titles[] = $title;

    return $titles;
}

$titles = ['Kindred', 'Kitchen'];
withTitle($titles, 'Beloved');
echo count($titles), " titles\n";

$titles = withTitle($titles, 'Beloved');
echo count($titles), " titles\n";
