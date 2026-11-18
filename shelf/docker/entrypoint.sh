#!/bin/sh
# Before Shelf answers its first request (chapter 78): the database gets every migration it doesn't
# have yet. Then the command of the image (php-fpm) takes over this process.
set -e
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
exec "$@"
