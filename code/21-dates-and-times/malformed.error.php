<?php

declare(strict_types=1);

$dueOn = new DateTimeImmutable('the first of Decembre');
echo $dueOn->format('Y-m-d'), "\n";
