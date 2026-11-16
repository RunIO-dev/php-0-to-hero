<?php

// Two small functions, each with one job. The second one uses the first.
function statusOf($copies)
{
    return $copies > 0 ? 'available' : 'all copies on loan';
}

function bookLine($title, $copies)
{
    return "- $title: " . statusOf($copies);
}

echo bookLine('Kindred', 0), "\n";
echo bookLine('Kitchen', 1), "\n";
