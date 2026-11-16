<?php

function readingDays($pages, $pagesPerDay)
{
    return (int) ceil($pages / $pagesPerDay);
}
