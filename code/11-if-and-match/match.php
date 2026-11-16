<?php

$genre = 'sf';

$shelfName = match ($genre) {
    'sf' => 'Science fiction',
    'crime' => 'Crime',
    'kids', 'teens' => 'Young readers',
    default => 'General',
};

echo $shelfName, "\n";
