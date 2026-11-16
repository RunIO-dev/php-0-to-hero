<?php

declare(strict_types=1);

/** @param array<string, string> $loans title => due date */
function dueDateOf(array $loans, string $title): ?string
{
    return $loans[$title] ?? null;
}

/** @param array<string, string> $loans title => due date */
function loanLine(array $loans, string $title): string
{
    $due = dueDateOf($loans, $title);

    return $due === null ? "$title: on the shelf" : "$title: due back on $due";
}
