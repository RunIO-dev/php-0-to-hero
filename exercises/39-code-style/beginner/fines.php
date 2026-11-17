<?php
declare(strict_types=1);
// Exercise 39, beginner: tidy a messy file.
// Bring this file into PER Coding Style, without changing what it prints.
// Use the fixer from the course folder:
//   vendor/bin/php-cs-fixer fix exercises/39-code-style/beginner/fines.php
// or change it by hand, then read what changed. Check your work: composer exercise 39 beginner
function lateFine( int $days , bool $isStaff=false ):int{
if($isStaff){return 0;}
  $fine=$days*20;
    return min($fine,500);
}
echo lateFine(3),"\n",lateFine(40),"\n",lateFine(3,true),"\n";
