<?php

declare(strict_types=1);

// Fibers (chapter 89, a short look): a function that can pause in the middle, and be resumed later, with
// its variables as they were. Libraries for asynchronous code build on them; application code rarely
// needs them directly.

$reader = new Fiber(static function (array $titles): string {
    foreach ($titles as $title) {
        $answer = Fiber::suspend($title); // pause: the caller gets $title
        echo "fiber: $title is $answer\n";
    }

    return 'all checked';
});

$title = $reader->start(['Kindred', 'Beloved']);
while (!$reader->isTerminated()) {
    echo "main: checking $title\n";
    $title = $reader->resume($title === 'Kindred' ? 'on the shelf' : 'lent');
}
echo 'main: ', $reader->getReturn(), "\n";
