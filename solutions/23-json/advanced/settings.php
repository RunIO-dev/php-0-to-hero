<?php

declare(strict_types=1);

const DEFAULT_SETTINGS = ['loanDays' => 14, 'feePerDay' => 50];

/** @return array{loanDays: int, feePerDay: int} */
function loadSettings(string $path): array
{
    $json = is_readable($path) ? file_get_contents($path) : false;
    if ($json === false) {
        throw new RuntimeException("Cannot read the settings from $path");
    }
    $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        throw new UnexpectedValueException('The settings must be a JSON object');
    }

    $settings = DEFAULT_SETTINGS;
    foreach (DEFAULT_SETTINGS as $name => $default) {
        if (!array_key_exists($name, $data)) {
            continue;
        }
        if (!is_int($data[$name])) {
            throw new UnexpectedValueException("The setting $name must be a whole number");
        }
        $settings[$name] = $data[$name];
    }

    return $settings;
}
