<?php

$isMember = true;
$copies = 2;
$hasOverdueBooks = false;

$canBorrow = $isMember && $copies > 0 && !$hasOverdueBooks;
var_dump($canBorrow);

$isLibrarian = false;
$canSeeAllLoans = $isLibrarian || $isMember;
var_dump($canSeeAllLoans);
