<?php

declare(strict_types=1);

// How CI decides (chapter 79): each step is a command, and its exit status says whether it worked:
// 0 is success, anything else a failure. The job stops at the first step that fails.

/**
 * Runs a command, and returns its exit status and what it printed, errors included.
 *
 * @param list<string> $command
 * @return array{int, string}
 */
function run(array $command): array
{
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    return [proc_close($process), $output];
}

$steps = [
    'tests' => 'echo "OK (141 tests, 472 assertions)\n";',
    'static analysis' => 'echo "Line 12: Method lend() has no return type.\n"; exit(1);',
    'code style' => 'echo "Found 0 of 83 files that can be fixed\n";',
];
foreach ($steps as $name => $code) {
    [$status, $output] = run([PHP_BINARY, '-r', $code]);
    echo "$name: exit $status, ", $output;
    if ($status !== 0) {
        echo "The job stops here, and fails.\n";
        break;
    }
}

// exit with a text prints it, and ends with status 0: for CI, a success.
[$status, $output] = run([PHP_BINARY, '-r', 'exit("The test database could not be migrated\n");']);
echo "exit(\"...\"): exit $status, ", $output;
[$status, $output] = run([PHP_BINARY, '-r', 'fwrite(STDERR, "The test database could not be migrated\n"); exit(1);']);
echo "exit(1): exit $status, ", $output;
