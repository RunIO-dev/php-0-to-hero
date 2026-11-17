<?php

declare(strict_types=1);

// The books, a page at a time (?page=2&per_page=3), filtered by author (?author=butler), sorted by
// title or year (?sort=year). The Link header names the other pages.

require_once __DIR__ . '/catalog.php';

const PER_PAGE = 3;
const MAX_PER_PAGE = 50;

/** What ?sort= may choose: a name from the address picks SQL written here, never SQL from the address. */
const SORTS = ['title' => 'title COLLATE NOCASE', 'year' => 'year, title COLLATE NOCASE'];

/** A whole number from the address, $default when it is missing, or null when it isn't a number. */
function number(string $name, int $default): ?int
{
    $value = $_GET[$name] ?? null;
    if ($value === null) {
        return $default;
    }

    return is_string($value) && preg_match('/^[0-9]{1,6}$/D', $value) === 1 ? (int) $value : null;
}

$page = number('page', 1);
$perPage = number('per_page', PER_PAGE);
$sort = is_string($_GET['sort'] ?? null) ? $_GET['sort'] : 'title';
$author = is_string($_GET['author'] ?? null) ? trim($_GET['author']) : '';
if ($page === null || $page < 1 || $perPage === null || $perPage < 1 || $perPage > MAX_PER_PAGE) {
    problem(400, 'Bad Request', 'page is a number from 1, per_page a number from 1 to ' . MAX_PER_PAGE . '.');
}
if (!isset(SORTS[$sort])) {
    problem(400, 'Bad Request', 'sort is title or year.');
}

$db = database();
// An empty filter switches itself off: (:author = '' OR ...). One statement for every request.
$filter = ['author' => $author, 'pattern' => '%' . addcslashes($author, '%_\\') . '%'];
$count = $db->prepare("SELECT COUNT(*) FROM books WHERE (:author = '' OR author LIKE :pattern ESCAPE '\\')");
$count->execute($filter);
$total = (int) $count->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));

$find = $db->prepare("SELECT title, author, year FROM books WHERE (:author = '' OR author LIKE :pattern ESCAPE '\\')
    ORDER BY " . SORTS[$sort] . ' LIMIT :limit OFFSET :offset');
$find->bindValue('author', $filter['author']);
$find->bindValue('pattern', $filter['pattern']);
$find->bindValue('limit', $perPage, PDO::PARAM_INT);
$find->bindValue('offset', ($page - 1) * $perPage, PDO::PARAM_INT);
$find->execute();

// Links to the first, previous, next and last page, with the same filter and sort.
$link = static fn(int $to, string $rel): string
    => '</books.php?' . http_build_query(array_filter(['author' => $author, 'sort' => $sort === 'title' ? '' : $sort]) + ['page' => $to, 'per_page' => $perPage]) . ">; rel=\"$rel\"";
$links = [$link(1, 'first')];
if ($page > 1) {
    $links[] = $link(min($page - 1, $pages), 'prev');
}
if ($page < $pages) {
    $links[] = $link($page + 1, 'next');
}
$links[] = $link($pages, 'last');
header('Link: ' . implode(', ', $links));

json(200, $find->fetchAll());
