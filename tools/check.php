<?php

// Setup check for PHP 0-to-hero. Run it with: php tools/check.php
// It checks what the course needs and prints the exact fix for anything missing.
// Written in old-style PHP on purpose, so that it also runs on an old PHP
// and can tell you to upgrade.

$needPhp = '8.5.0';
$problems = 0;
$os = defined('PHP_OS_FAMILY') ? PHP_OS_FAMILY : PHP_OS;

function report($ok, $what, $fix)
{
    echo ($ok ? '  ok    ' : '  FIX   ') . $what . "\n";
    if (!$ok) {
        echo '        ' . str_replace("\n", "\n        ", $fix) . "\n";
    }
    return $ok ? 0 : 1;
}

function extensionFix($ext, $os)
{
    if ($os === 'Windows') {
        return "Open your php.ini (run: php --ini to see where it is),\n"
            . "remove the ; in front of the line extension=$ext, save, and run this check again.";
    }
    if ($os === 'Darwin') {
        return "Install PHP with Homebrew (brew install php); it includes $ext.";
    }
    $debian = array('pdo_sqlite' => 'sqlite3', 'dom' => 'xml', 'xmlwriter' => 'xml');
    $fedora = array('pdo_sqlite' => 'pdo', 'dom' => 'xml', 'xmlwriter' => 'xml', 'tokenizer' => 'common', 'ctype' => 'common');
    $d = isset($debian[$ext]) ? $debian[$ext] : $ext;
    $f = isset($fedora[$ext]) ? $fedora[$ext] : $ext;
    return "Debian or Ubuntu: sudo apt install php-$d\nFedora: sudo dnf install php-$f";
}

echo "PHP 0-to-hero setup check\n\n";

$problems += report(
    version_compare(PHP_VERSION, $needPhp, '>='),
    'PHP ' . PHP_VERSION . " (the course needs $needPhp or newer)",
    "Install PHP 8.5 or newer: see chapter 2 for your system.\n"
    . 'Or use Docker, which needs no PHP on your computer: docker compose run --rm php php tools/check.php'
);

// mbstring: text in any language (chapter 8). pdo_sqlite: the database part.
// ctype, dom, tokenizer and xmlwriter: needed by PHPUnit and our tools.
foreach (array('mbstring', 'pdo_sqlite', 'ctype', 'dom', 'tokenizer', 'xmlwriter') as $ext) {
    $problems += report(extension_loaded($ext), "extension $ext", extensionFix($ext, $os));
}

$shown = ini_get('display_errors');
$problems += report(
    in_array(strtolower((string) $shown), array('1', 'on', 'stdout', 'stderr'), true),
    'error messages are shown (display_errors)',
    "While learning you want to see every error. In your php.ini (php --ini shows where),\n"
    . "set: display_errors = On   and   error_reporting = E_ALL"
);

$composer = false;
$paths = explode(PATH_SEPARATOR, (string) getenv('PATH'));
foreach ($paths as $dir) {
    foreach (array('composer', 'composer.bat', 'composer.phar') as $name) {
        if ($dir !== '' && is_file($dir . DIRECTORY_SEPARATOR . $name)) {
            $composer = true;
        }
    }
}
// Composer installs PHPUnit, which runs the exercise tests (chapter 3).
$problems += report(
    $composer,
    'Composer (it installs the tools that check your exercises)',
    "Install it from https://getcomposer.org/download/ (chapter 3 shows how)."
);
$root = dirname(__DIR__);
$problems += report(
    is_file($root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'),
    'the course tools are installed (vendor/)',
    "In the course folder, run: composer install"
);

echo "\n" . ($problems === 0
    ? "Everything the course needs is ready.\n"
    : "$problems thing(s) to fix. Fix them, then run this check again.\n");

exit($problems === 0 ? 0 : 1);
