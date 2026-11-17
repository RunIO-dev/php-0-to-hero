<?php

declare(strict_types=1);

abstract class Notice
{
    abstract public function body(): string;
}

final class Welcome extends Notice {}
