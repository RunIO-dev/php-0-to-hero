<?php

declare(strict_types=1);

// Exercise 47, advanced: the rules of a sign-up form, which register.php uses.

const FIELDS = ['name', 'email', 'year', 'terms'];

/**
 * @param array<mixed> $post
 *
 * @return array{name: string, email: string, year: string, terms: string}
 */
function values(array $post): array
{
    $text = fn(string $name): string => is_string($post[$name] ?? null) ? trim($post[$name]) : '';

    return ['name' => $text('name'), 'email' => $text('email'), 'year' => $text('year'), 'terms' => $text('terms')];
}

/**
 * @param array{name: string, email: string, year: string, terms: string} $values
 *
 * @return array<string, string>
 */
function errors(array $values): array
{
    $errors = [];
    $length = mb_strlen($values['name']);
    if ($length < 1 || $length > 50) {
        $errors['name'] = 'From 1 to 50 characters.';
    }
    if (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'A valid email address, please.';
    }
    if (filter_var($values['year'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900, 'max_range' => 2026]]) === false) {
        $errors['year'] = 'A year from 1900 to 2026.';
    }
    if ($values['terms'] !== 'yes') {
        $errors['terms'] = 'Please accept the rules.';
    }

    return $errors;
}
