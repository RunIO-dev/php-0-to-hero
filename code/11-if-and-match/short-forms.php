<?php

$copies = 0;
$returnedAt = null;

$label = $copies > 0 ? 'available' : 'on loan';
echo "Status: $label\n";

echo 'Returned: ', $returnedAt ?? 'not yet', "\n";
