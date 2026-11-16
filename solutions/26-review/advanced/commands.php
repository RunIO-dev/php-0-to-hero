<?php

declare(strict_types=1);

/** @param list<string> $args */
function run(array $args): string
{
    $command = $args[0] ?? 'help';

    return match ($command) {
        'help' => "Commands: help, hello <name>, count <words>\n",
        'hello' => 'Hello, ' . ($args[1] ?? 'reader') . "!\n",
        'count' => (count($args) - 1) . " words\n",
        default => throw new InvalidArgumentException("Unknown command \"$command\""),
    };
}
