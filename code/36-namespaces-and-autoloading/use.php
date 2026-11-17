<?php

declare(strict_types=1);

namespace Library\Catalog {
    final readonly class Book
    {
        public function __construct(public string $title) {}
    }
}

namespace Shop {
    final readonly class Book
    {
        public function __construct(public string $title, public int $cents) {}
    }
}

// The global namespace: use gives a full name a short one, as gives it another.

namespace {
    use Library\Catalog\Book;
    use Shop\Book as ShopBook;

    $lent = new Book('Kindred');
    $sold = new ShopBook('Kindred', 1299);

    echo $lent::class, ': ', $lent->title, "\n";
    echo $sold::class, ': ', $sold->title, ', ', $sold->cents, " cents\n";
}
