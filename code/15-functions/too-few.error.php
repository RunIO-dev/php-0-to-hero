<?php

function lateFee($daysLate)
{
    return $daysLate * 50;
}

echo lateFee(), "\n";
