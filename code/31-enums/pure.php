<?php

declare(strict_types=1);

// An enum lists every possible value. There is no other.
enum Section
{
    case Fiction;
    case History;
    case Science;
}

function floorOf(Section $section): int
{
    return match ($section) {
        Section::Fiction => 1,
        Section::History, Section::Science => 2,
    };
}

function isFiction(Section $section): bool
{
    return $section === Section::Fiction;
}

$section = Section::History;

echo "$section->name is on floor ", floorOf($section), "\n";
var_dump(isFiction($section));
