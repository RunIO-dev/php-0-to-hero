<?php

declare(strict_types=1);

// DQL: queries about classes and their properties. Doctrine turns them into SQL, with the JOIN that
// the relation needs, and gives back objects.

use Library\Book;
use Library\Loan;

require_once __DIR__ . '/entities.php';

$entities = entities();
$kindred = new Book('Kindred', 'Octavia E. Butler');
$sula = new Book('Sula', 'Toni Morrison');
$entities->persist($kindred);
$entities->persist($sula);
$entities->persist(new Loan($kindred, 'Amira', new DateTimeImmutable('2026-12-01')));
$entities->persist(new Loan($sula, 'Amira', new DateTimeImmutable('2026-11-20')));
$entities->persist(new Loan($sula, 'Jonas', new DateTimeImmutable('2026-11-25')));
$entities->flush();

$query = $entities->createQuery('SELECT l, b FROM Library\Loan l JOIN l.book b WHERE l.member = :member ORDER BY l.due')
    ->setParameter('member', 'Amira');
echo $query->getSQL(), "\n";

foreach ($query->getResult() as $loan) {
    assert($loan instanceof Loan);
    echo "{$loan->book->title}, due {$loan->due->format('Y-m-d')}\n";
}

$onLoan = $entities->createQueryBuilder()
    ->select('b.title, COUNT(l.id) AS loans')
    ->from(Loan::class, 'l')
    ->join('l.book', 'b')
    ->groupBy('b.id')
    ->orderBy('loans', 'DESC')
    ->getQuery()
    ->getArrayResult();
print_r($onLoan);
