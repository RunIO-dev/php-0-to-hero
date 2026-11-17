<?php

declare(strict_types=1);

namespace Tools;

/**
 * Web examples (Part 7 on). An example x.php with an x.http next to it is served by PHP's
 * built-in web server, as a learner runs it (php -S localhost:8000), and the requests in
 * x.http are sent to it. Its .out holds each request and response, written like curl -v:
 *
 *   > POST /add.php                     the request, with its headers and body
 *   > Content-Type: application/x-www-form-urlencoded
 *   >
 *   > title=Kindred
 *
 *   < HTTP/1.1 303 See Other            the status, and the headers that matter
 *   < Location: /books.php
 *
 *   <body>                              the body, as the browser gets it
 *
 * The .http file: requests separated by lines starting with ###. A request is "METHOD /path",
 * then header lines, a blank line and the body. Lines starting with # are comments, except:
 *   # docroot public                    serve this folder (php -S ... -t public)
 *   # router public/index.php           send every request to this script
 *   # env LIBRARY_NAME=North Library    an environment variable for the server, as Docker sets them
 *   # run php shelf.php import          a command run in the folder before the server starts (chapter 63),
 *                                       as a learner would: "php <file> [arguments] [< file]"; the .out
 *                                       starts with each command and what it printed
 *   # capture token /name="_token" value="([^"]+)"/   remember group 1 of the response (headers and body)
 * In a multipart/form-data body, a line "< cover.png" sends the bytes of that file (an upload).
 * {{token}} in a later request is replaced by the remembered value, and the value is shown
 * as {{token}} in the output, so a random token gives the same .out every time. Cookies are
 * kept between the requests of one file, like a browser does; long cookie values are shown as ….
 */
final class Web
{
    /** Response headers worth reading; Date, Host and X-Powered-By change and would make noise. */
    private const array SHOWN = ['content-type', 'location', 'allow', 'set-cookie', 'content-disposition',
        'content-security-policy', 'x-content-type-options', 'x-frame-options', 'referrer-policy', 'www-authenticate', 'link', 'x-total-count'];

    public static function has(string $file): bool
    {
        return is_file(substr($file, 0, -4) . '.http');
    }

    /**
     * @return array{docroot: string, router: ?string, env: array<string, string>, runs: list<string>, requests: list<array{method: non-empty-string, path: string, headers: list<string>, body: string, captures: array<string, string>}>}
     */
    public static function parse(string $text): array
    {
        $docroot = '.';
        $router = null;
        $env = [];
        $runs = [];
        $requests = [];
        foreach (preg_split('/^###.*$/m', $text) ?: [] as $block) {
            $lines = explode("\n", str_replace("\r\n", "\n", $block));
            $captures = [];
            $request = null;
            $inBody = false;
            $body = [];
            foreach ($lines as $line) {
                if ($request === null) {
                    if (preg_match('/^# docroot (\S+)$/', $line, $m) === 1) {
                        $docroot = $m[1];
                    } elseif (preg_match('/^# router (\S+)$/', $line, $m) === 1) {
                        $router = $m[1];
                    } elseif (preg_match('/^# env ([A-Z][A-Z0-9_]*)=(.*)$/', $line, $m) === 1) {
                        $env[$m[1]] = $m[2];
                    } elseif (preg_match('/^# run (php .+)$/', $line, $m) === 1) {
                        $runs[] = $m[1];
                    } elseif (preg_match('/^# capture (\w+) (.+)$/', $line, $m) === 1) {
                        $captures[$m[1]] = $m[2];
                    } elseif (preg_match('~^(GET|POST|PUT|PATCH|DELETE|HEAD) (/\S*)$~', $line, $m) === 1) {
                        $request = ['method' => $m[1], 'path' => $m[2], 'headers' => [], 'body' => '', 'captures' => $captures];
                    } elseif (trim($line) !== '' && !str_starts_with($line, '#')) {
                        throw new \RuntimeException("A request starts with a method and a path, such as GET /books.php: $line");
                    }
                    continue;
                }
                if ($inBody) {
                    $body[] = $line;
                } elseif (trim($line) === '') {
                    $inBody = true;
                } elseif (preg_match('/^# capture (\w+) (.+)$/', $line, $m) === 1) {
                    $request['captures'][$m[1]] = $m[2];
                } else {
                    $request['headers'][] = $line;
                }
            }
            if ($request !== null) {
                $request['body'] = rtrim(implode("\n", $body), "\n");
                $requests[] = $request;
            }
        }
        foreach ([$docroot, $router ?? '.'] as $path) {
            if (str_contains($path, '..') || str_starts_with($path, '/')) {
                throw new \RuntimeException("docroot and router must be inside the example's folder: $path");
            }
        }

        return ['docroot' => $docroot, 'router' => $router, 'env' => $env, 'runs' => $runs, 'requests' => $requests];
    }

    /** The command a learner types to serve the example, from its folder. */
    public static function command(string $file): string
    {
        $site = self::parse((string) file_get_contents(substr($file, 0, -4) . '.http'));

        return 'php -S localhost:8000' . ($site['docroot'] === '.' ? '' : ' -t ' . $site['docroot']) . ($site['router'] === null ? '' : ' ' . $site['router']);
    }

    /** Every request of the example's .http file, sent to a server started for it, and the answers. */
    public static function output(string $file): string
    {
        return self::exchange(dirname($file), (string) file_get_contents(substr($file, 0, -4) . '.http'))['out'];
    }

    /**
     * Serves a copy of $dir, so that forms that change files leave the example as it was,
     * sends the requests written in $http (the .http format above), and returns the answers.
     *
     * @return array{responses: list<array{status: int, headers: array<string, string>, body: string}>, out: string}
     */
    public static function exchange(string $dir, string $http): array
    {
        $site = self::parse($http);
        $copy = sys_get_temp_dir() . '/web-' . bin2hex(random_bytes(4));
        Run::copy($dir, $copy);
        $ran = '';
        foreach ($site['runs'] as $line) {
            $ran .= self::run($line, $copy, $site['env']);
        }
        [$server, $port] = self::serve($copy, $site['docroot'], $site['router'], $site['env']);
        try {
            $cookies = [];
            $values = [];
            $blocks = [];
            $responses = [];
            foreach ($site['requests'] as $request) {
                $fill = static fn(string $text): string => (string) preg_replace_callback(
                    '/\{\{(\w+)\}\}/',
                    static fn(array $m): string => $values[$m[1]] ?? throw new \RuntimeException("{{{$m[1]}}} was never captured"),
                    $text,
                );
                $sent = ['method' => $request['method'], 'path' => $fill($request['path']),
                    'headers' => array_map($fill, $request['headers']), 'body' => $fill($request['body'])];
                if (preg_grep('~^content-type:\s*multipart/form-data~i', $sent['headers']) !== []) {
                    $sent['body'] = self::multipart($sent['body'], $dir);
                }
                $response = self::send($port, $sent, $cookies);
                foreach ($request['captures'] as $name => $pattern) {
                    if (@preg_match($pattern, implode("\n", $response['headers']) . "\n\n" . $response['body'], $m) !== 1 || !isset($m[1])) {
                        throw new \RuntimeException("capture $name: $pattern found nothing in the response to {$request['path']}");
                    }
                    $values[$name] = $m[1];
                }
                $blocks[] = self::format($request, $response);
                $responses[] = self::structured($response);
            }
            $out = ($ran === '' ? '' : "$ran\n") . implode("\n", $blocks);
            foreach ($values as $name => $value) {
                $out = str_replace($value, '{{' . $name . '}}', $out);
            }

            return ['responses' => $responses, 'out' => str_replace([$copy . '/', $dir . '/'], '', $out)];
        } finally {
            proc_terminate($server);
            proc_close($server);
            Run::remove($copy);
        }
    }

    /**
     * A multipart/form-data body as written in a .http file, ready to send: lines end in CRLF, as
     * the format requires, and a line "< cover.png" is replaced by the bytes of that file, from
     * the example's folder (as JetBrains' HTTP client writes it).
     */
    private static function multipart(string $body, string $dir): string
    {
        $parts = [];
        foreach (explode("\n", $body) as $line) {
            if (preg_match('/^< (\S+)$/', $line, $m) === 1) {
                if (str_contains($m[1], '..') || str_starts_with($m[1], '/') || !is_file("$dir/{$m[1]}")) {
                    throw new \RuntimeException("< {$m[1]}: no such file in the example's folder");
                }
                $parts[] = (string) file_get_contents("$dir/{$m[1]}");
            } else {
                $parts[] = $line;
            }
        }

        return implode("\r\n", $parts) . "\r\n";
    }

    /**
     * @param array{status: string, headers: list<string>, body: string} $response
     * @return array{status: int, headers: array<string, string>, body: string} header names in lower case
     */
    private static function structured(array $response): array
    {
        $headers = [];
        foreach ($response['headers'] as $line) {
            [$name, $value] = array_map('trim', explode(':', $line, 2)) + [1 => ''];
            $headers[strtolower($name)] = $value;
        }

        return ['status' => (int) (explode(' ', $response['status'])[1] ?? 0), 'headers' => $headers, 'body' => $response['body']];
    }

    /**
     * Runs one "# run" command in the copy, without a shell, and returns it with what it printed.
     * A command that fails stops the check: the requests after it would test the wrong thing.
     *
     * @param array<string, string> $env
     */
    private static function run(string $line, string $dir, array $env): string
    {
        $words = Examples::words($line);
        $stdin = null;
        if (count($words) >= 2 && $words[count($words) - 2] === '<') {
            $stdin = "$dir/" . array_pop($words);
            array_pop($words);
        }
        $script = $words[1] ?? '';
        if (preg_match('~^[\w.-]+(/[\w.-]+)*$~', $script) !== 1 || str_contains($script, '..') || !is_file("$dir/$script")
            || ($stdin !== null && (preg_match('~/[\w.-]+$~', $stdin) !== 1 || !is_file($stdin)))) {
            throw new \RuntimeException("# run: only php <file of the folder> [arguments] [< file of the folder]: $line");
        }
        $run = Run::capture([PHP_BINARY, ...array_slice($words, 1)], $dir, $env, stdin: $stdin);
        if ($run['code'] !== 0) {
            throw new \RuntimeException("# run $line failed:\n" . $run['out'] . $run['err']);
        }

        return "$ $line\n" . $run['out'] . $run['err'];
    }

    /**
     * The PHP files a .http file in $dir, or in the folder above it (with # docroot), requests, or
     * serves through its router: they are pages, checked through the requests, not run on their own.
     *
     * @return list<string> absolute paths
     */
    public static function served(string $dir): array
    {
        $files = [];
        foreach ([...glob($dir . '/*.http') ?: [], ...glob(dirname($dir) . '/*.http') ?: []] as $http) {
            $base = dirname($http);
            $site = self::parse((string) file_get_contents($http));
            if ($site['router'] !== null) {
                $files[] = "$base/{$site['router']}";
            }
            foreach ($site['requests'] as $request) {
                $path = (string) parse_url($request['path'], PHP_URL_PATH);
                if (str_ends_with($path, '.php')) {
                    $files[] = rtrim("$base/{$site['docroot']}", '/.') . $path;
                }
            }
        }

        return array_values(array_unique(array_map(static fn(string $f): string => (string) preg_replace('~/\./~', '/', $f), $files)));
    }

    /**
     * @param array{method: string, path: string, headers: list<string>, body: string} $request as written in the .http file
     * @param array{status: string, headers: list<string>, body: string} $response
     */
    private static function format(array $request, array $response): string
    {
        $lines = ["> {$request['method']} {$request['path']}"];
        foreach ($request['headers'] as $header) {
            $lines[] = "> $header";
        }
        if ($request['body'] !== '') {
            $lines[] = '>';
            foreach (explode("\n", $request['body']) as $line) {
                $lines[] = rtrim("> $line");
            }
        }
        $lines[] = '';
        $lines[] = "< {$response['status']}";
        foreach ($response['headers'] as $header) {
            $lines[] = "< $header";
        }

        $body = $response['body'];
        if (str_contains($body, "\0") || !mb_check_encoding($body, 'UTF-8')) {
            $body = '(' . strlen($body) . ' bytes of binary data)'; // an image, as the browser gets it
        }

        return implode("\n", $lines) . "\n" . ($body === '' ? '' : "\n" . $body . (str_ends_with($body, "\n") ? '' : "\n"));
    }

    /**
     * @param array<string, string> $env
     *
     * @return array{resource, int} the server process and its port
     */
    private static function serve(string $dir, string $docroot, ?string $router, array $env): array
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        if ($socket === false) {
            throw new \RuntimeException('No free port for the web server');
        }
        $port = (int) substr((string) strrchr((string) stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);
        $command = [PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=1', '-S', "127.0.0.1:$port", '-t', $docroot];
        if ($router !== null) {
            $command[] = $router;
        }
        $server = proc_open($command, [0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes, $dir, $env === [] ? null : array_merge(getenv(), $env));
        if ($server === false) {
            throw new \RuntimeException('Could not start the web server');
        }
        for ($try = 0; $try < 100; $try++) {
            $probe = @stream_socket_client("tcp://127.0.0.1:$port", $errno, $error, 0.1);
            if ($probe !== false) {
                fclose($probe);

                return [$server, $port];
            }
            usleep(50_000);
        }
        proc_terminate($server);
        throw new \RuntimeException('The web server did not start');
    }

    /**
     * @param array{method: non-empty-string, path: string, headers: list<string>, body: string} $request
     * @param array<string, string> $cookies name => value, kept between requests like a browser
     * @return array{status: string, headers: list<string>, body: string}
     */
    private static function send(int $port, array $request, array &$cookies): array
    {
        $curl = curl_init("http://127.0.0.1:$port{$request['path']}");
        $headers = $request['headers'];
        if ($cookies !== []) {
            $headers[] = 'Cookie: ' . implode('; ', array_map(static fn(string $k, string $v): string => "$k=$v", array_keys($cookies), $cookies));
        }
        $received = [];
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $request['method'],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADERFUNCTION => static function (\CurlHandle $curl, string $line) use (&$received): int {
                $received[] = rtrim($line, "\r\n");

                return strlen($line);
            },
        ]);
        if ($request['body'] !== '') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request['body']);
        }
        $body = curl_exec($curl);
        if ($body === false) {
            throw new \RuntimeException('Request failed: ' . curl_error($curl));
        }
        $status = (string) array_shift($received);
        $shown = [];
        foreach ($received as $line) {
            [$name, $value] = array_map('trim', explode(':', $line, 2)) + [1 => ''];
            if (strtolower($name) === 'set-cookie' && preg_match('/^([^=;]+)=([^;]*)/', $value, $m) === 1) {
                if ($m[2] === '' || stripos($value, 'expires=Thu, 01 Jan 1970') !== false) {
                    unset($cookies[$m[1]]); // the server deleted it
                } else {
                    $cookies[$m[1]] = $m[2];
                }
                if (strlen($m[2]) >= 20) {
                    $line = "$name: {$m[1]}=…" . substr($value, strlen($m[0]));
                }
            }
            $line = (string) preg_replace('/; expires=[^;]+/i', '; expires=…', $line); // a date that changes with every run
            if (in_array(strtolower($name), self::SHOWN, true)) {
                $shown[] = $line;
            }
        }

        return ['status' => $status, 'headers' => $shown, 'body' => (string) $body];
    }
}
