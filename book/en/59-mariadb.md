# 59. MariaDB with Docker, and least privilege

## Goals

- You will be able to run MariaDB in Docker, next to PHP, with one command.
- You will be able to connect to it with the same PDO code you wrote for SQLite.
- You will be able to give the application a database user that can change rows, and nothing else.
- You will be able to recognise where SQLite and MariaDB differ.

## Before you start

- Chapter 45: Docker and `compose.yaml`.
- Chapters 54 and 55: PDO, prepared statements and CRUD.

## The idea

SQLite is a file that one program opens. A **database server** is a program of its own, which many programs, on many computers, reach over the network. Websites with several servers, or with a lot of traffic, use one. The most common free ones are **MySQL**, **MariaDB** and **PostgreSQL**. MariaDB started in 2009 as a copy of MySQL, made by MySQL's first developers, and is still compatible with it in almost everything; it is open source under the GPL. What this chapter shows works the same on MySQL.

With PDO, moving from SQLite to MariaDB changes the connection, not the code: the same `prepare()`, `execute()` and `rowCount()`. The SQL differs in places, mostly in `CREATE TABLE`: each database names its types and options its own way.

A server brings something SQLite doesn't have: **users** with **privileges**. A user logs in with a password, and may do only what it was granted. The rule here is **least privilege**: give each program the smallest set of rights it needs. A website reads and writes rows; it never needs to drop a table. If an attacker finds a way to run SQL through the website, a user that can't drop tables can't drop them for the attacker either. So the course database has two users:

- `shelf_admin` may change tables. Migrations (chapter 58) run as this user.
- `shelf_app` may `SELECT`, `INSERT`, `UPDATE` and `DELETE` rows, and nothing else. The application connects as this user.

## In code

### MariaDB in Docker

`compose.yaml` gets a fourth service, `db`, and passes the two users' passwords to the PHP containers as environment variables:

<!-- include: compose.yaml -->

- `image: mariadb:11.8` is MariaDB's long-term support version, with security fixes for years.
- The service has no `ports`: only the course's containers reach it, by the name `db`. Nothing on your network can.
- `MARIADB_DATABASE: shelf` makes an empty database called `shelf` the first time.
- `MARIADB_RANDOM_ROOT_PASSWORD`: the all-powerful `root` user gets a random password that nobody uses. The two users below are all you need.
- The data lives in a Docker volume, `db-data`, so it survives restarts. `docker compose down -v` deletes it.
- The passwords in `x-db-passwords` are for this database on your computer only, which nobody else can reach. Real secrets never go into a file in Git; chapter 65 shows where they go.

The users are made by a script that MariaDB's image runs once, when the database is new, `docker/db/users.sh`:

<!-- include: docker/db/users.sh -->

- `CREATE USER 'shelf_app'@'%'`: the user may connect from any host (`%`), here meaning any of the course's containers.
- `GRANT ... ON shelf.*` gives rights on every table in the database `shelf`, and nowhere else.

The Dockerfile adds PHP's driver for MySQL and MariaDB, `pdo_mysql`:

<!-- include: Dockerfile -->

Start the database, and wait until it is ready:

```
docker compose build php
docker compose up -d --wait db
```

### Connecting

The examples connect through a helper, `code/59-mariadb/db.php`:

<!-- include: code/59-mariadb/db.php -->

- The DSN `mysql:host=db;dbname=shelf;charset=utf8mb4` names the driver, the host, the database and the character set. `mysql` is the driver's name for MySQL and MariaDB alike.
- `charset=utf8mb4` makes every character of every language arrive as it was sent. MySQL's older `utf8` stores only part of Unicode; `utf8mb4` is all of it.
- The password comes from the environment, with `getenv()`, never from the code.
- `ATTR_EMULATE_PREPARES => false` makes PDO use the server's real prepared statements: the SQL and the values travel separately, as in chapter 54. Without it, PDO for MySQL puts escaped values into the SQL itself. That is safe too, but real prepared statements are the simpler guarantee.
- `freshLibrary()` makes the books table again, as `shelf_admin`, so each example starts from the same data. The table, in MariaDB's words, `code/59-mariadb/library.sql`:

<!-- include: code/59-mariadb/library.sql -->

- `INT UNSIGNED ... AUTO_INCREMENT PRIMARY KEY` is SQLite's `INTEGER PRIMARY KEY`.
- `VARCHAR(200)` is text of up to 200 characters. MariaDB refuses longer text.
- `ENGINE = InnoDB` is the storage engine with transactions and foreign keys; it is the default, and named here so the file says it.
- `utf8mb4_unicode_ci` compares text by Unicode's rules, without case (`ci`: case-insensitive), in every language. SQLite's `NOCASE` knows only English letters.

<!-- include: code/59-mariadb/connect.php -->

`CURRENT_USER()` shows who we are: `shelf_app`, from any host.

### The same code

<!-- include: code/59-mariadb/same-code.php -->

The statements are those of chapters 54 and 55, and so are the answers: `kindred` finds *Kindred*, because the column compares without case; `rowCount()` says whether a copy was lent.

### Least privilege

<!-- include: code/59-mariadb/least-privilege.php -->

- `SHOW GRANTS` lists what the current user may do. A second line, `GRANT USAGE`, means only "may connect"; it is left out here because it shows the password's hash.
- Deleting rows works: the application may do that.
- `DROP TABLE` and `CREATE TABLE` are refused. The message names the computer the request came from; the example replaces that part with `…`, since it differs on every network.

### A strict database

<!-- include: code/59-mariadb/strict.php -->

MariaDB runs in **strict mode** by default: a value of the wrong type, or too long, is refused instead of being changed quietly. The `CHECK` on the year holds too. Make sure a server you use has it on: some older setups turn it off.

## Common mistakes

### localhost inside a container

<!-- include: code/59-mariadb/localhost.error.php -->

⚠ Inside the `php` container, `localhost` is that container, which has no database. `No such file or directory` means PDO looked for a local MariaDB socket file and found none.

**Fix:** use the service's name, `host=db`. Outside Docker, it would be the server's address. The stack trace shows another thing: PHP hides the password, as `Object(SensitiveParameterValue)`, because PDO marks that parameter as sensitive.

### One user with every right

Connecting as `root`, or as a user with `ALL PRIVILEGES`, makes every bug and every injection as powerful as the database's owner.

**Fix:** a user for the application with row rights only, and one for migrations, as here.

### SQL that only one database speaks

`INSERT ... ON CONFLICT` (chapter 54) is SQLite's and PostgreSQL's; MariaDB writes `INSERT ... ON DUPLICATE KEY UPDATE`. `AUTO_INCREMENT`, `STRICT` and `COLLATE NOCASE` exist in only one of them. Code that must run on both has to choose, as the intermediate exercise does.

**Fix:** use the common part of SQL where you can (`SELECT`, `INSERT`, `UPDATE`, `DELETE`, `JOIN`), and keep the rest in one place, such as the migrations.

## Principle check

**Secure by default:** the application's user can't change tables, the database can't be reached from outside Docker, and `root` has a random password. **KISS:** one command starts the database, with its users. **YAGNI:** *Shelf* stays on SQLite until it needs a server (see Shelf).

## Security

- **Least privilege** limits what an attack can do once it gets in; it doesn't replace prepared statements.
- **Not on the network:** a database should never be reachable from the internet. Here it has no published port; on a server, a firewall and a private network do the same (Part 12).
- **Passwords** come from the environment, never from code in Git. The ones in `compose.yaml` are for a local database that nobody else can reach.
- **Keep it updated:** an LTS version gets security fixes; `docker compose pull db` gets them.

## Shelf

*Shelf* stays on SQLite. It is one person's library on one computer, and SQLite serves that well: no server to run, back up or update, and a database that is one file. MariaDB becomes worth it when several servers share one database, or many people write at once.

Nothing in *Shelf*'s classes would change for MariaDB: `PdoBookRepository`, `PdoMemberRepository` and `PdoLoanRepository` use only common SQL. Three things would change:

- `openDatabase()` would build a `mysql:` DSN, with the password from the environment, as in `db.php`.
- The migrations would be written in MariaDB's words (`AUTO_INCREMENT`, `VARCHAR`), and run as `shelf_admin`, while the application connects as `shelf_app`.
- `inTransaction()`, `rowCount()` and the guarded `UPDATE` of chapter 57 work the same, because InnoDB has transactions too.

Part 12, which puts *Shelf* on a server, comes back to this choice.

## Exercises

The exercises are in `exercises/59-mariadb/`. They need MariaDB running: `docker compose up -d --wait db`. Check your work with:

```
composer exercise 59 beginner
```

1. **Beginner, `connect.php`:** the application's connection, with the password from the environment.
2. **Intermediate, `portable.php`:** add or update in one statement, on SQLite and on MariaDB.
3. **Advanced, `schema.php`:** one table with the same rules, in the words of each database.

## Summary

- MariaDB is a database server; with PDO, the same code runs on it, only the DSN changes.
- Use `charset=utf8mb4` in the DSN, and real prepared statements.
- Give the application a user with row rights only; migrations run as another user.
- Keep the database off the network, and passwords out of Git.
- SQL dialects differ most in `CREATE TABLE` and in upserts; keep those in one place.

## Further reading

- MariaDB documentation, account management (CREATE USER, GRANT): https://mariadb.com/docs/server/reference/sql-statements/account-management-sql-statements
- MariaDB documentation, SQL_MODE (strict mode): https://mariadb.com/docs/server/server-management/variables-and-modes/sql_mode
- Docker Hub, the official MariaDB image: https://hub.docker.com/_/mariadb
- PHP manual, PDO MySQL: https://www.php.net/manual/en/ref.pdo-mysql.php
