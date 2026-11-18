<?php

declare(strict_types=1);

// A queue and its worker, by hand (chapter 85): what Messenger does for Shelf. A message is a row in a
// table. "send" adds one; "work" takes them one by one, oldest first, and does each. One that fails is
// tried again later, three times at most; after that it waits in "failed" for a person to look at it.
//   php queue.php send "Remind Amira"     php queue.php work     php queue.php status
// A message that contains "offline" fails, as an email does while the mail server is down.

const MAX_TRIES = 3;
const RETRY_AFTER = 60; // seconds; each try waits twice as long as the one before

$db = new PDO('sqlite:' . __DIR__ . '/queue.sqlite', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY, body TEXT NOT NULL,
    tries INTEGER NOT NULL DEFAULT 0, available_at INTEGER NOT NULL, failed INTEGER NOT NULL DEFAULT 0)');

function handle(string $body): void
{
    if (str_contains($body, 'offline')) {
        throw new RuntimeException('the mail server does not answer');
    }
}

switch ($argv[1] ?? '') {
    case 'send':
        $db->prepare('INSERT INTO messages (body, available_at) VALUES (?, ?)')->execute([$argv[2], time()]);
        echo "queued #{$db->lastInsertId()}: {$argv[2]}\n";
        break;

    case 'work':
        $next = $db->prepare('SELECT id, body, tries FROM messages WHERE failed = 0 AND available_at <= ? ORDER BY id LIMIT 1');
        while ($next->execute([time()]) && ($message = $next->fetch(PDO::FETCH_ASSOC)) !== false) {
            /** @var array{id: int, body: string, tries: int} $message */
            try {
                handle($message['body']);
                $db->prepare('DELETE FROM messages WHERE id = ?')->execute([$message['id']]);
                echo "#{$message['id']} {$message['body']}: done\n";
            } catch (RuntimeException $e) {
                $tries = $message['tries'] + 1;
                $wait = RETRY_AFTER * 2 ** ($tries - 1);
                $db->prepare('UPDATE messages SET tries = ?, available_at = ?, failed = ? WHERE id = ?')
                    ->execute([$tries, time() + $wait, $tries >= MAX_TRIES ? 1 : 0, $message['id']]);
                echo "#{$message['id']} {$message['body']}: {$e->getMessage()}, try $tries of " . MAX_TRIES
                    . ($tries >= MAX_TRIES ? ": failed\n" : ", again in $wait s\n");
            }
        }
        echo "nothing more to do now\n";
        break;

    case 'status':
        foreach ($db->query('SELECT id, body, tries, failed FROM messages ORDER BY id') ?: [] as $row) {
            $tried = $row['tries'] === 1 ? 'once' : "{$row['tries']} times";
            echo "#{$row['id']} {$row['body']}: " . ($row['failed'] ? 'failed' : 'waiting') . ", tried $tried\n";
        }
        break;

    default:
        fwrite(STDERR, "Usage: php queue.php send <message> | work | status\n");
        exit(1);
}
