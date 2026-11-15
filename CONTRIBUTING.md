# Contributing

Corrections are welcome: a wrong fact, an unclear sentence, a broken example, a
better Arabic or German term.

- **Small fixes:** open a pull request.
- **Larger changes** (a new chapter, a different approach): open an issue first.

## Before a pull request

Run every check. They are the same checks CI runs:

```
docker compose run --rm php composer install
docker compose run --rm php composer verify
docker compose run --rm php85 composer verify
```

The rules the checks enforce, and the ones they can't, are in
[docs/standards.md](docs/standards.md). In short:

- Code in the book is always included from a tested file in `code/`, never pasted.
- Every example has a `.out` file with its exact output.
- Every exercise test fails on the starter and passes on the solution.
- Code that shows what *not* to do goes in a file named `*.bad.php`.
- No secrets, no tracking, and no promotion of products or services.

By contributing, you agree that your text is licensed under CC BY 4.0 and your
code under the MIT licence, like the rest of the course.
