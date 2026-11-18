# PHP 0 to Hero

A free course that takes you from no programming at all to a secure PHP web
application online: a free book, with every line of code tested.

- **89 chapters in 14 parts.** See the [curriculum](docs/curriculum.md).
- **One project grows through the course:** *Shelf*, a small lending library. It starts
  as a script on the command line and ends as a Symfony app in production.
- **PHP 8.6**, with every example also tested on PHP 8.5.
- **Secure, simple, no repetition:** these principles are taught where they apply, and
  the checks in `tools/verify` enforce them on every example.

The course is being written. Chapters appear in `book/en/` as they are finished.

## Start here

1. Install PHP 8.5 or newer (chapter 2 shows how on Windows, macOS and Linux).
2. Download this repository and run the setup check from its folder:

   ```
   php tools/check.php
   ```

   It tells you exactly what to fix, if anything.

**Or use Docker**, which needs no PHP on your computer and gives you exactly the
setup the examples are tested with:

```
docker compose run --rm php php tools/check.php
```

## Exercises

Every chapter has three exercises: beginner, intermediate and advanced. Write your
code in `exercises/<chapter>/<level>/`, then run its tests:

```
composer install
composer exercise 06 beginner
```

The solutions are in `solutions/`. Try first: the tests tell you when you are done.

## Folders

| Folder | What is in it |
|---|---|
| `book/en/` | the chapters, one file each |
| `code/` | every example from the book, with its expected output (`.out`) |
| `exercises/`, `solutions/` | the exercises with their tests, and the solutions |
| `shelf/` | the project, newest state; each chapter that changes it keeps its version in `code/NN-slug/shelf/` |
| `glossary/ar.json` | the Arabic terms, with their sources |
| `tools/` | the setup check, the checks, and the book build |
| `docs/` | the curriculum and the standards the course is written to |

## Licence

- The text of the book is licensed under [CC BY 4.0](LICENSE).
- The code is licensed under the [MIT licence](LICENSE-CODE).
- Material adapted from other sources is credited in [ATTRIBUTION.md](ATTRIBUTION.md).
