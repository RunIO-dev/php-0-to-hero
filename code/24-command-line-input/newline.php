<?php

declare(strict_types=1);

echo 'Lend it? (yes/no) ';
$answer = fgets(STDIN);

// The typed line ends with the Enter key: "yes\n" is not "yes".
var_dump($answer === 'yes');
var_dump(trim((string) $answer) === 'yes');
