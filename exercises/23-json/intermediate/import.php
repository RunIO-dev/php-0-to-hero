<?php

declare(strict_types=1);

// Exercise 23, intermediate: titles from JSON.
//
// Write a function titlesFromJson(string $json): array that reads a JSON list
// of books and returns their titles:
//   '[{"title": "Kindred"}, {"title": "Kitchen"}]'  ->  ['Kindred', 'Kitchen']
// Broken JSON must throw a JsonException, not return an empty list.
//
// Check your work: composer exercise 23 intermediate
