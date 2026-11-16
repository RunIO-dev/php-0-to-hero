<?php

$genre = 'poetry';

$shelfName = match ($genre) {
    'sf' => 'Science fiction',
    'crime' => 'Crime',
};

echo $shelfName, "\n";
