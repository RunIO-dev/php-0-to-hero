<?php

declare(strict_types=1);

// Exercise 38, advanced: a small composer audit. A solution.

/**
 * @param list<array{package: string, fixed: string, title: string}> $advisories
 * @return list<string>
 */
function affected(string $lockFile, array $advisories): array
{
    $lines = [];
    foreach (installed($lockFile) as $name => $version) {
        foreach ($advisories as $advisory) {
            if ($advisory['package'] === $name && version_compare($version, $advisory['fixed'], '<')) {
                $lines[] = "$name $version: {$advisory['title']} (fixed in {$advisory['fixed']})";
            }
        }
    }
    sort($lines);

    return $lines;
}

/**
 * Every package in the lock file, also the ones only for development.
 *
 * @return array<string, string> name => version
 */
function installed(string $lockFile): array
{
    $lock = json_decode((string) file_get_contents($lockFile), true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($lock)) {
        throw new UnexpectedValueException("$lockFile is not a lock file");
    }
    $installed = [];
    foreach ([...$lock['packages'] ?? [], ...$lock['packages-dev'] ?? []] as $package) {
        if (is_array($package) && is_string($package['name'] ?? null) && is_string($package['version'] ?? null)) {
            $installed[$package['name']] = $package['version'];
        }
    }

    return $installed;
}
