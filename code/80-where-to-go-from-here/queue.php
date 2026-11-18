<?php

declare(strict_types=1);

// A queue (chapter 80): the page answers at once, and the slow work (an email, a report) waits in a
// queue for a worker, another process. Here an SplQueue stands for the queue, which in production is a
// table in the database or a message broker, so that it outlives both processes.

final readonly class SendReminder
{
    public function __construct(
        public string $member,
        public string $title,
    ) {}
}

/** @var SplQueue<SendReminder> $queue */
$queue = new SplQueue();

// The request: it puts two messages into the queue, and answers.
$queue->enqueue(new SendReminder('Amira', 'Kindred'));
$queue->enqueue(new SendReminder('Jonas', 'Beloved'));
echo "page: answered, {$queue->count()} reminders queued\n";

// The worker, later: it takes each message, and handles it. A message that fails goes back into the queue,
// for another try; the first try to reach Jonas's mail server fails here.
$tries = ['Jonas' => 0];
while (!$queue->isEmpty()) {
    $reminder = $queue->dequeue();
    try {
        if ($reminder->member === 'Jonas' && $tries['Jonas']++ === 0) {
            throw new RuntimeException('the mail server does not answer');
        }
        echo "worker: reminded {$reminder->member} of {$reminder->title}\n";
    } catch (RuntimeException $e) {
        echo "worker: {$reminder->member} failed ({$e->getMessage()}), queued again\n";
        $queue->enqueue($reminder);
    }
}
