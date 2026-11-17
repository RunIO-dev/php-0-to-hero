<?php

declare(strict_types=1);

// Where inheritance fits well: kinds of exceptions.
class ShelfException extends RuntimeException {}

final class BookNotFound extends ShelfException {}

final class NoCopyLeft extends ShelfException {}

// never: this function doesn't return; it always throws.
function lend(string $title): never
{
    if ($title === 'Kitchen') {
        throw new NoCopyLeft("All copies of \"$title\" are on loan");
    }

    throw new BookNotFound("There is no book titled \"$title\"");
}

foreach (['Kitchen', 'Sula'] as $title) {
    try {
        lend($title);
    } catch (ShelfException $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";
    }
}
