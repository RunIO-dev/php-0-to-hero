<?php

declare(strict_types=1);

// Exercise 36, advanced: an autoloader of your own.
//
// Composer writes the autoloader for you. To see that there is no magic,
// write a function registerAutoloader(string $prefix, string $dir): void.
// It registers an autoloader with spl_autoload_register: a function that PHP
// calls with the full name of a class it doesn't know yet. The autoloader
// - turns the class name into a path, as in the intermediate exercise;
// - loads that file with require, if the file exists;
// - does nothing for another prefix or a missing file, so that other
//   autoloaders can try.
//
// Check your work: composer exercise 36 advanced
