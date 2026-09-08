#!/bin/bash
# =============================================================================
#  Server-side auto-deploy for way2party  (no cPanel API token required)
# -----------------------------------------------------------------------------
#  Run from a cPanel cron job every few minutes. It fetches origin/<branch>
#  and, ONLY when there is a new commit, syncs the code and runs the Laravel
#  release steps. Untracked files (.env, storage/, Modules/, uploads) are never
#  touched. If someone left uncommitted edits on the server it refuses to run.
#
#  Cron entry (cPanel -> Cron Jobs):
#    */5 * * * * /home/CPUSER/way2party/deploy/server-pull.sh >> /home/CPUSER/way2party/storage/logs/deploy.log 2>&1
#
#  Override the paths below with env vars in the cron line if needed, e.g.:
#    */5 * * * * APP_DIR=/home/x/app PHP=/opt/cpanel/ea-php83/root/usr/bin/php /home/x/app/deploy/server-pull.sh ...
# =============================================================================

main() {
    set -euo pipefail

    APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
    PHP="${PHP:-/usr/local/bin/php}"
    COMPOSER="${COMPOSER:-/opt/cpanel/composer/bin/composer}"
    BRANCH="${BRANCH:-main}"

    cd "$APP_DIR"

    # --- new commits? ---------------------------------------------------------
    git fetch --quiet origin "$BRANCH"
    local local_sha remote_sha
    local_sha="$(git rev-parse HEAD)"
    remote_sha="$(git rev-parse "origin/${BRANCH}")"
    [ "$local_sha" = "$remote_sha" ] && exit 0

    # --- one deploy at a time -----------------------------------------------
    exec 9>"${APP_DIR}/storage/framework/deploy.lock"
    flock -n 9 || { echo "$(date '+%F %T')  deploy already running, skip"; exit 0; }

    # --- never clobber un-pushed server edits ------------------------------
    if ! git diff --quiet || ! git diff --cached --quiet; then
        echo "$(date '+%F %T')  ABORT: uncommitted changes in $APP_DIR — commit/push them to GitHub first"
        git status --short
        exit 1
    fi

    echo "$(date '+%F %T')  deploying ${local_sha:0:7} -> ${remote_sha:0:7}"

    git reset --hard "origin/${BRANCH}"

    "$PHP" -d memory_limit=-1 "$COMPOSER" install --no-dev --prefer-dist --optimize-autoloader --no-interaction

    "$PHP" artisan migrate --force

    "$PHP" artisan optimize:clear
    "$PHP" artisan config:cache
    "$PHP" artisan route:cache
    "$PHP" artisan view:cache
    "$PHP" artisan event:cache || true
    "$PHP" artisan storage:link || true

    # queue workers / horizon, if used:
    # "$PHP" artisan queue:restart || true

    echo "$(date '+%F %T')  done -> now at $(git rev-parse --short HEAD)"
}

main "$@"
