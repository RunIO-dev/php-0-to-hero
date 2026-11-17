<?php

declare(strict_types=1);

// The shape of a JSON object, as programs rely on it: each field's name and type.

/**
 * What is wrong with $data compared with $shape, such as ['title' => 'string', 'isbn' => '?string']:
 * every field must be there, with its type ("?" also allows null), and there must be no other field.
 *
 * @param array<string, string> $shape
 * @return list<string> empty when $data has the shape
 */
function shapeErrors(array $shape, mixed $data): array
{
    if (!is_array($data) || array_is_list($data) && $data !== []) {
        return ['not an object, but ' . get_debug_type($data)];
    }
    $errors = [];
    foreach ($shape as $field => $type) {
        if (!array_key_exists($field, $data)) {
            $errors[] = "$field is missing";
            continue;
        }
        $actual = get_debug_type($data[$field]);
        if ($actual !== ltrim($type, '?') && !($actual === 'null' && str_starts_with($type, '?'))) {
            $errors[] = "$field is $actual, not $type";
        }
    }
    foreach (array_diff(array_keys($data), array_keys($shape)) as $extra) {
        $errors[] = "$extra is not expected";
    }

    return $errors;
}
