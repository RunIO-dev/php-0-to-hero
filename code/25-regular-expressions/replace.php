<?php

declare(strict_types=1);

$typed = "  Things   Fall\tApart ";

// \s+ is one or more spaces, tabs or line breaks: replace each run with one space.
echo '"', preg_replace('/\s+/', ' ', trim($typed)), "\"\n";

// preg_split splits at every match: here at commas, with or without spaces.
print_r(preg_split('/\s*,\s*/', 'history ,africa,  novel'));
