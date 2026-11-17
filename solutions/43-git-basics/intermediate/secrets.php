<?php

declare(strict_types=1);

// Exercise 43, intermediate: find secrets before they are committed. A solution.

/**
 * @param array<string, string> $files path => content
 * @return list<string>
 */
function findSecrets(array $files): array
{
    $found = [];
    foreach ($files as $path => $content) {
        $name = basename($path);
        if (preg_match('/^\.env(\..+)?$/', $name) === 1 && !in_array($name, ['.env.example', '.env.dist'], true)) {
            $found[] = "$path: an environment file";
        }
        if (preg_match('/\.(pem|key)$/', $name) === 1) {
            $found[] = "$path: a key file";
        }
        if (str_contains($content, '-----BEGIN') && str_contains($content, 'PRIVATE KEY-----')) {
            $found[] = "$path: a private key";
        }
        foreach (explode("\n", $content) as $i => $line) {
            if (preg_match('/[\w$"\']*(password|secret|token|api_?key)\w*["\']?\s*[:=]\s*["\']?[^"\'\s]{8,}/i', $line) === 1) {
                $found[] = "$path line " . ($i + 1) . ': a password or key';
            }
        }
    }

    return $found;
}
