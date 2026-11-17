<?php

declare(strict_types=1);

// What htmlspecialchars changes: the five characters that mean something in HTML.

foreach (['Tom & Jerry', '<b>bold</b>', 'Say "hello"', "Butler's Kindred", 'Kindred'] as $text) {
    echo str_pad($text, 18), ' => ', htmlspecialchars($text), "\n";
}
