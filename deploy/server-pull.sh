#!/bin/bash
# =============================================================================
#  Server-side auto-deploy for way2party   (no cPanel API token required)
# -----------------------------------------------------------------------------
#  Runs from a cPanel cron job every couple of minutes. Fetches origin/<branch>
#  and, ONLY when there is a new commit, syncs the code and runs the Laravel
#  release steps. Untracked files (.env, storage/, Modules/, public/modules/,
#  uploads, deploy/.deploy-env, Passport keys) are never touched. Refuses to
#  run if someone left uncommitted edits to TRACKED files on the server.
#
#  One-time setup:  run  deploy/server-setup.sh  first   (see DEPLOYMENT.md).
#
#  Cron entry (cPanel -> Cron Jobs), every 2 minutes:
#    */2 * * * * /home/CPUSER/way2party/deploy/server-pull.sh >> /home/CPUSER/way2party/storage/logs/deploy.log 2>&1
#
#  Paths come from deploy/.deploy-env (written by server-setup.sh). You can also
#  override them inline in the cron line:  PHP=/path COMPOSER=/path BRANCH=main
# =============================================================================

main() {
    set -euo pipefail

    APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
    cd "$APP_DIR"

    # server-specific binary paths, written by deploy/server-setup.sh
    # shellcheck disable=SC1091
    [ -f "$APP_DIR/deploy/.deploy-env" ] && . "$APP_DIR/deploy/.deploy-env"
    PHP="${PHP:-$(command -v php 2>/dev/null || echo /usr/local/bin/php)}"
    COMPOSER="${COMPOSER:-/opt/cpanel/composer/bin/composer}"
    BRANCH="${BRANCH:-main}"

    # --- new commits? ------------------------------------------------------
    git fetch --quiet origin "$BRANCH"
    local local_sha remote_sha
    local_sha="$(git rev-parse HEAD)"
    remote_sha="$(git rev-parse "origin/${BRANCH}")"
    [ "$local_sha" = "$remote_sha" ] && exit 0

    # --- one deploy at a time -------------------------------------------
    mkdir -p "${APP_DIR}/storage/framework"
    exec 9>"${APP_DIR}/storage/framework/deploy.lock"
    flock -n 9 || { echo "$(date '+%F %T')  deploy already running, skip"; exit 0; }

    # --- never clobber un-pushed server edits -------------------------
    if ! git diff --quiet || ! git diff --cached --quiet; then
        echo "$(date '+%F %T')  ABORT: uncommitted changes to tracked files in $APP_DIR"
        echo "  -> commit & push them to GitHub from your PC, or discard: git checkout -- <path>"
        git status --short
        exit 1
    fi

    echo "$(date '+%F %T')  deploying ${local_sha:0:7} -> ${remote_sha:0:7}"

    git reset --hard "origin/${BRANCH}"

    if [ -x "$COMPOSER" ] || command -v "$COMPOSER" >/dev/null 2>&1; then
        "$PHP" -d memory_limit=-1 "$COMPOSER" install --no-dev --prefer-dist \
               --optimize-autoloader --no-interaction
    else
        echo "$(date '+%F %T')  WARN: composer not found at '$COMPOSER' — skipping composer install"
    fi

    "$PHP" artisan migrate --force

    # optimize:clear FIRST so a previous bad cache can never wedge the site
    "$PHP" artisan optimize:clear
    "$PHP" artisan config:cache
    "$PHP" artisan route:cache
    "$PHP" artisan view:cache
    "$PHP" artisan event:cache || true
    "$PHP" artisan storage:link || true
    "$PHP" artisan queue:restart 2>/dev/null || true

    echo "$(date '+%F %T')  done -> now at $(git rev-parse --short HEAD)"
}

main "$@"
