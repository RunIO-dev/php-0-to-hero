<?php

declare(strict_types=1);

/** @return array{date: string, action: string, title: string, email: string}|null */
function parseLogLine(string $line): ?array
{
    $pattern = '/^(?<date>\d{4}-\d{2}-\d{2}) (?<action>LEND|RETURN) "(?<title>[^"]+)" (?<email>\S+)$/D';
    if (preg_match($pattern, $line, $m) !== 1 || filter_var($m['email'], FILTER_VALIDATE_EMAIL) === false) {
        return null;
    }

    return ['date' => $m['date'], 'action' => $m['action'], 'title' => $m['title'], 'email' => $m['email']];
}
