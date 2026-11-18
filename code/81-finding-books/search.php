<?php

declare(strict_types=1);

// One search box for the title, the author and the ISBN (chapter 81). What the visitor typed goes
// into LIKE through a placeholder, with LIKE's own wildcards escaped: a % they type is a letter.

require_once __DIR__ . '/catalogue.php';

/** "%part%", with %, _ and the escape letter itself taken as letters. */
function pattern(string $text): string
{
    return '%' . addcslashes(mb_strtolower($text), '%_\\') . '%';
}

/** The digits of a search that looks like an ISBN, "978-0-8070" or "9780807": else ''. */
function isbnDigits(string $text): string
{
    $digits = str_replace(['-', ' '], '', $text);

    return preg_match('/^\d{4,13}$/D', $digits) === 1 ? $digits : '';
}

/** @return list<string> the titles found, by title */
function search(PDO $db, string $text): array
{
    $sql = "SELECT title FROM books WHERE LOWER(title) LIKE :text ESCAPE '\\' OR LOWER(author) LIKE :text ESCAPE '\\'";
    $values = ['text' => pattern($text)];
    if (isbnDigits($text) !== '') {
        $sql .= ' OR isbn LIKE :digits';
        $values['digits'] = '%' . isbnDigits($text) . '%';
    }
    $found = $db->prepare("$sql ORDER BY title");
    $found->execute($values);

    return $found->fetchAll(PDO::FETCH_COLUMN);
}

$db = catalogue();
foreach (['BUTLER', 'wood', '978-0-8070', '0802142443', '%', 'o_e'] as $text) {
    printf("%-12s %s\n", $text, implode(', ', search($db, $text)) ?: '(nothing)');
}
