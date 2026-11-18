#!/bin/sh
# The database's two users (chapter 59). MariaDB's image runs this file once, when it starts
# with an empty data folder, while only it can reach the database.
#   shelf_admin: changes tables, for migrations.
#   shelf_app:   reads and writes rows, and nothing else. The application uses this one.
set -eu
mariadb --protocol=socket -uroot -p"$MARIADB_ROOT_PASSWORD" <<SQL
CREATE USER 'shelf_admin'@'%' IDENTIFIED BY '$SHELF_ADMIN_PASSWORD';
GRANT ALL PRIVILEGES ON shelf.* TO 'shelf_admin'@'%';
CREATE USER 'shelf_app'@'%' IDENTIFIED BY '$SHELF_APP_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE ON shelf.* TO 'shelf_app'@'%';
SQL
