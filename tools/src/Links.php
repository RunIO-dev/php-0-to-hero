<?php

declare(strict_types=1);

namespace Tools;

/** Every web link in the book answers (docs/standards.md §6.6). Run weekly, not on every push. */
final class Links
{
    /** @return list<string> */
    public static function check(string $root): array
    {
        $urls = [];
        foreach (Run::files($root . '/book', '.md') as $file) {
            preg_match_all('~https?://[^\s)>\]"\'`]+~', (string) file_get_contents($file), $m);
            foreach ($m[0] as $url) {
                $url = rtrim($url, '.,;:');
                if (!self::isExample($url)) {
                    $urls[$url][] = substr($file, strlen($root) + 1);
                }
            }
        }
        $problems = [];
        foreach ($urls as $url => $files) {
            $status = self::status($url);
            if ($status < 200 || $status >= 400) {
                $problems[] = "$url (HTTP $status) in " . implode(', ', array_unique($files));
            }
        }

        return $problems;
    }

    /**
     * An address that stands for one in the text, not a page to read: the learner's own computer,
     * a domain reserved for examples (RFC 2606), or an address with no host.
     */
    public static function isExample(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return !is_string($host) || $host === '' || in_array($host, ['localhost', '127.0.0.1'], true)
            || preg_match('~(^|\.)(example|test|invalid|localhost)$|(^|\.)example\.(com|org|net)$~', $host) === 1;
    }

    private static function status(string $url): int
    {
        $context = stream_context_create(['http' => [
            'method' => 'HEAD',
            'timeout' => 15,
            'ignore_errors' => true,
            'user_agent' => 'php-0-to-hero link check',
        ]]);
        $headers = get_headers($url, false, $context);
        if ($headers === false) {
            return 0;
        }
        $status = 0;
        foreach ($headers as $line) {
            if (preg_match('~^HTTP/\S+\s+(\d{3})~', $line, $m) === 1) {
                $status = (int) $m[1]; // the last one, after redirects
            }
        }

        return $status;
    }
}
