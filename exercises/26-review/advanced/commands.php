<?php

declare(strict_types=1);

// Exercise 26, advanced: a command list in one place.
//
// run() lists its commands twice: in the in_array() check and in the match.
// Adding a command means changing both, and forgetting one gives a confusing
// error. Rewrite run() so the commands are listed only in the match, and an
// unknown command throws the same InvalidArgumentException as now.
// The behaviour must stay the same; the tests try every command.
//
// Check your work: composer exercise 26 advanced

/** @param list<string> $args */
function run(array $args): string
{
    $command = $args[0] ?? 'help';
    if (!in_array($command, ['help', 'hello', 'count'], true)) {
        throw new InvalidArgumentException("Unknown command \"$command\"");
    }

    return match ($command) {
        'help' => "Commands: help, hello <name>, count <words>\n",
        'hello' => 'Hello, ' . ($args[1] ?? 'reader') . "!\n",
        'count' => (count($args) - 1) . " words\n",
    };
}
