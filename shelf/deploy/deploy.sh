#!/bin/sh
# Runs on the server (chapter 79), as the user "deploy", in the folder that holds compose.prod.yaml
# and the server's own .env (SHELF_IMAGE, SHELF_HOST, SHELF_APP_SECRET; mode 600, never in Git).
# CI logs in with a key that may run only this script, and sends the commit to deploy:
#   command="/home/deploy/shelf/deploy.sh",restrict ssh-ed25519 AAAA... shelf-ci   (in ~/.ssh/authorized_keys)
set -eu
cd "$(dirname "$0")"

# The commit comes from CI over SSH: a full commit hash, and nothing else, so that the key can't
# be used to run any other command.
tag=${SSH_ORIGINAL_COMMAND:-${1:-}}
case "$tag" in
    *[!0-9a-f]* | "") echo "Usage: deploy.sh <commit, 40 hex digits>" >&2; exit 2 ;;
esac
[ ${#tag} -eq 40 ] || { echo "Usage: deploy.sh <commit, 40 hex digits>" >&2; exit 2; }

export SHELF_TAG="$tag"
docker compose -f compose.prod.yaml pull
# The new containers take over; the migrations run before PHP-FPM starts (docker/entrypoint.sh).
docker compose -f compose.prod.yaml up -d --no-build --remove-orphans
docker image prune -f >/dev/null
echo "Deployed $tag"
