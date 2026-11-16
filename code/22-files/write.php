<?php

declare(strict_types=1);

// A file in the system's folder for temporary files.
$path = sys_get_temp_dir() . '/shelf-returns.txt';

file_put_contents($path, "Kindred\n");                  // creates or replaces the file
file_put_contents($path, "Kitchen\n", FILE_APPEND);     // adds to the end

echo file_get_contents($path);

unlink($path); // deletes the file again
