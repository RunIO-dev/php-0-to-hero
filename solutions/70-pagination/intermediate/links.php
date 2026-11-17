<?php

declare(strict_types=1);

/**
 * The Link header's value for page $page of $pages, as an API sends it:
 *   </books.php?author=butler&page=1&per_page=2>; rel="first", </books.php?author=butler&page=1&per_page=2>; rel="prev", ...
 * - first and last always; prev only after page 1; next only before the last page.
 * - Each address keeps the filters (left out when they are empty), then page and per_page.
 * - A page past the end gets "prev" to the last page, so a program can find its way back.
 *
 * @param array<string, string> $filters such as ['author' => 'butler']
 */
function pageLinks(string $path, array $filters, int $page, int $pages, int $perPage): string
{
    $link = static fn(int $to, string $rel): string
        => "<$path?" . http_build_query(array_filter($filters, static fn(string $v): bool => $v !== '') + ['page' => $to, 'per_page' => $perPage]) . ">; rel=\"$rel\"";
    $links = [$link(1, 'first')];
    if ($page > 1) {
        $links[] = $link(min($page - 1, $pages), 'prev');
    }
    if ($page < $pages) {
        $links[] = $link($page + 1, 'next');
    }
    $links[] = $link($pages, 'last');

    return implode(', ', $links);
}
