<?php

$isMember = true;
$copiesOnShelf = 1;
$hasOverdueBooks = false;

$canBorrow = $isMember && $copiesOnShelf > 0 && !$hasOverdueBooks;
var_dump($canBorrow);
