# Exercise 43, beginner: a .gitignore

`.gitignore` in this folder belongs to a small web project. Write the rules, so
that Git leaves out:

- the folder `vendor/` at the top of the project, but not a folder called `vendor`
  deeper down, such as `templates/vendor/`;
- the file `.env`, which holds secrets; but `.env.example`, which shows which
  settings exist without their values, must stay;
- the folder `var/` at the top: caches and logs;
- every file ending in `.log`, at any depth;
- PHPUnit's cache, `.phpunit.cache/`.

Then check your work: `composer exercise 43 beginner`
