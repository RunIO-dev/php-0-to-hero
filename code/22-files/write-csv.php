<?php

declare(strict_types=1);

$path = sys_get_temp_dir() . '/shelf-members.csv';
$file = fopen($path, 'w');

fputcsv($file, ['name', 'email'], escape: '');
fputcsv($file, ['Amira', 'amira@example.org'], escape: '');
fputcsv($file, ['Ana, Jr.', 'ana@example.org'], escape: '');

fclose($file);

echo file_get_contents($path);
unlink($path);
