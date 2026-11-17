<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Tools\Run;

final class GitignoreTest extends TestCase
{
    private const array IGNORED = [
        'vendor/autoload.php', '.env', 'var/cache/routes.php', 'var/log/dev.log', 'error.log',
        'public/uploads/import.log', '.phpunit.cache/test-results',
    ];
    private const array KEPT = [
        '.env.example', 'composer.json', 'composer.lock', 'src/Book.php', 'public/index.php',
        'templates/vendor/list.html', 'src/Var/Value.php',
    ];

    public function testGitLeavesOutExactlyTheRightFiles(): void
    {
        $dir = sys_get_temp_dir() . '/gitignore-' . bin2hex(random_bytes(4));
        mkdir($dir);
        try {
            copy(subject(__DIR__, '.gitignore'), "$dir/.gitignore");
            foreach ([...self::IGNORED, ...self::KEPT] as $path) {
                if (!is_dir(dirname("$dir/$path"))) {
                    mkdir(dirname("$dir/$path"), 0o777, true);
                }
                touch("$dir/$path");
            }
            Run::capture(['git', 'init', '-q'], $dir);
            $run = Run::capture(['git', 'check-ignore', ...self::IGNORED, ...self::KEPT], $dir);
        } finally {
            Run::remove($dir);
        }
        $ignored = array_values(array_filter(explode("\n", $run['out'])));

        self::assertSame([], array_values(array_diff(self::IGNORED, $ignored)), 'These should be ignored.');
        self::assertSame([], array_values(array_intersect(self::KEPT, $ignored)), 'These should stay.');
    }
}
