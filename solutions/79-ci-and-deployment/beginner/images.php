<?php

declare(strict_types=1);

// The name of an image that CI pushes to GitHub's registry (chapter 79), as Shelf's workflow builds it:
//   imageName("RunIO-dev/shelf", "app", "8f3c...")  ->  "ghcr.io/runio-dev/shelf-app:8f3c..."
// - the repository is "owner/name" (letters, digits, - _ .), in lower case in the name: the registry wants it so;
// - the target is "app" or "web", the two stages that run;
// - the commit is a full commit hash, 40 hexadecimal digits, in lower case in the name.
// Anything else: InvalidArgumentException. A name built from a bad value is pushed, and deployed.

function imageName(string $repository, string $target, string $commit): string
{
    if (preg_match('~^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$~', $repository) !== 1) {
        throw new InvalidArgumentException("Not a repository: $repository");
    }
    if (!in_array($target, ['app', 'web'], true)) {
        throw new InvalidArgumentException("Not a target: $target");
    }
    if (preg_match('/^[0-9a-f]{40}$/i', $commit) !== 1) {
        throw new InvalidArgumentException("Not a commit: $commit");
    }

    return 'ghcr.io/' . strtolower($repository) . "-$target:" . strtolower($commit);
}
