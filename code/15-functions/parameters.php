<?php

// $name is a parameter: it gets its value from the call.
function greet($name)
{
    echo "Welcome to the library, $name!\n";
}

greet('Amira');
greet('Jonas');

// Several parameters: the values go in the same order.
function shelfLabel($number, $genre)
{
    echo "Shelf $number: $genre\n";
}

shelfLabel(3, 'Science fiction');
