<?php

declare(strict_types=1);

function registerAutoloader(string $prefix, string $dir): void
{
    spl_autoload_register(function (string $class) use ($prefix, $dir): void {
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = $dir . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}
