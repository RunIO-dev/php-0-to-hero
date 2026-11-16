<?php

declare(strict_types=1);

// $ also matches before a line break at the very end. The D flag stops that.
$typed = "10115\n";
echo preg_match('/^\d{5}$/', $typed), "\n";
echo preg_match('/^\d{5}$/D', $typed), "\n";
