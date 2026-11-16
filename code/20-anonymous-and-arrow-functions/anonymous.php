<?php

declare(strict_types=1);

// A function without a name is a value, stored in a variable like any other.
$shout = function (string $text): string {
    return mb_strtoupper($text) . '!';
};

echo $shout('quiet, please'), "\n";

// It can be passed to another function.
function twice(callable $action, string $text): string
{
    return $action($action($text));
}

echo twice($shout, 'quiet'), "\n";
