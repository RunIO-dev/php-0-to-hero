<?php

declare(strict_types=1);

abstract class Notice
{
    abstract public function body(): string;
}

$notice = new Notice();
