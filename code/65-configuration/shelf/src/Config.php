<?php

declare(strict_types=1);

namespace Shelf;

use UnexpectedValueException;

/**
 * Shelf's settings (chapter 65), read once when Shelf starts, and checked there: a setting that is
 * missing or wrong stops Shelf with a message that names it, before anything else happens. Three
 * layers, the later one winning: .env (in Git, no secrets), .env.local (this computer only, not in
 * Git), and the real environment variables that a server or Docker sets.
 */
final readonly class Config
{
    private const array ENVIRONMENTS = ['development', 'production'];

    public function __construct(public string $environment, public string $database, public string $covers) {}

    /** The settings for the Shelf in the folder $dir. */
    public static function load(string $dir): self
    {
        $local = "$dir/.env.local";
        $settings = [...readEnvFile("$dir/.env"), ...(is_file($local) ? readEnvFile($local) : []), ...self::environment()];

        return self::fromSettings($settings, $dir);
    }

    /** @param array<string, string> $settings */
    public static function fromSettings(array $settings, string $dir): self
    {
        $environment = self::required($settings, 'SHELF_ENV');
        if (!in_array($environment, self::ENVIRONMENTS, true)) {
            throw new UnexpectedValueException("SHELF_ENV must be development or production, not \"$environment\"");
        }

        return new self($environment, self::path($dir, self::required($settings, 'SHELF_DATABASE')), self::path($dir, self::required($settings, 'SHELF_COVERS')));
    }

    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    /** @param array<string, string> $settings */
    private static function required(array $settings, string $name): string
    {
        $value = trim($settings[$name] ?? '');
        if ($value === '') {
            throw new UnexpectedValueException("The setting $name is missing: add it to .env, or set it in the environment");
        }

        return $value;
    }

    /** A path from the settings: as it is when it starts with /, otherwise inside Shelf's folder. */
    private static function path(string $dir, string $path): string
    {
        return str_starts_with($path, '/') ? $path : "$dir/$path";
    }

    /** @return array<string, string> the environment variables whose names start with SHELF_ */
    private static function environment(): array
    {
        return array_filter(getenv(), static fn(string $name): bool => str_starts_with($name, 'SHELF_'), ARRAY_FILTER_USE_KEY);
    }
}
