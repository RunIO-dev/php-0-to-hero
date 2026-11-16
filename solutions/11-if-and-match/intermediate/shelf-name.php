<?php

$genre = 'crime';

$shelfName = match ($genre) {
    'sf' => 'Science fiction',
    'crime' => 'Crime',
    'poetry' => 'Poetry',
    'kids', 'teens' => 'Young readers',
    default => 'General',
};

echo $shelfName, "\n";
