<?php

declare(strict_types=1);

function dueDate(DateTimeImmutable $lentOn, int $days = 14): DateTimeImmutable
{
    return $lentOn->modify("+$days days");
}
