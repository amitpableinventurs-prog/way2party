#!/bin/bash
# =============================================================================
#  Server-side auto-deploy for way2party   (no cPanel API token required)
# -----------------------------------------------------------------------------
#  Runs from a cPanel cron job every couple of minutes. Fetches origin/<branch>
#  and, ONLY when there is a new commit, syncs the code and runs the Laravel
#  release steps.
#
#  NEVER touched: .env, storage/, Modules/, public/modules/, public/images/upload,
#  vendor/, bootstrap/cache, Passport keys, and the two server-specific files
#  listed in PRESERVE below (.htaccess, installer/.lic) — those are backed up and
#  restored around the git sync so a deploy can never take the live site down or
#  break the CodeCanyon licence.
#
#  Refuses to run if someone left uncommitted edits to OTHER tracked files.
#
#  Cron entry (cPanel -> Cron Jobs), every 2 minutes:
#    */2 * * * * /home/way2party/public_html/deploy/server-pull.sh >> /home/way2party/public_html/storage/logs/deploy.log 2>&1
#
#  Optional overrides in deploy/.deploy-env or inline in the cron line:
#    PHP=/path   COMPOSER=/path   BRANCH=main
# =============================================================================

# server-specific files that must survive every deploy unchanged
PRESERVE=".htaccess installer/.lic"

main() {
    set -euo pipefail

    APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
    cd "$APP_DIR"

    # shellcheck disable=SC1091
    [ -f "$APP_DIR/deploy/.deploy-env" ] && . "$APP_DIR/deploy/.deploy-env"
    PHP="${PHP:-$(command -v php 2>/dev/null || echo /usr/local/bin/php)}"
    COMPOSER="${COMPOSER:-/opt/cpanel/composer/bin/composer}"
    BRANCH="${BRANCH:-main}"

    # --- new commits? ----------------------------------------------------------
    git fetch --quiet origin "$BRANCH"
    local local_sha remote_sha
    local_sha="$(git rev-parse HEAD)"
    remote_sha="$(git rev-parse "origin/${BRANCH}")"
    [ "$local_sha" = "$remote_sha" ] && exit 0

    # --- one deploy at a time ------------------------------------------------
    mkdir -p "${APP_DIR}/storage/framework"
    exec 9>"${APP_DIR}/storage/framework/deploy.lock"
    flock -n 9 || { echo "$(date '+%F %T')  deploy already running, skip"; exit 0; }

    # --- never clobber un-pushed server edits ----------------------------
    if ! git diff --quiet || ! git diff --cached --quiet; then
        echo "$(date '+%F %T')  ABORT: uncommitted changes to tracked files in $APP_DIR"
        echo "  -> commit & push them to GitHub, or discard: git checkout -- <path>"
        git status --short
        exit 1
    fi

    echo "$(date '+%F %T')  deploying ${local_sha:0:7} -> ${remote_sha:0:7}"

    # --- back up the server-specific files -------------------------------
    local keep="${APP_DIR}/storage/framework/deploy-preserve"
    mkdir -p "$keep"
    local f
    for f in $PRESERVE; do
        [ -f "$APP_DIR/$f" ] && cp -p "$APP_DIR/$f" "$keep/${f//\//__}"
    done

    git reset --hard "origin/${BRANCH}"

    # --- restore them (whatever the repo now says) -----------------------
    for f in $PRESERVE; do
        if [ -f "$keep/${f//\//__}" ]; then
            mkdir -p "$APP_DIR/$(dirname "$f")"
            cp -p "$keep/${f//\//__}" "$APP_DIR/$f"
        fi
    done

    # --- composer: only when composer.lock actually changed --------------
    if ! git diff --quiet "$local_sha" "$remote_sha" -- composer.lock composer.json; then
        if [ -x "$COMPOSER" ] || command -v "$COMPOSER" >/dev/null 2>&1; then
            "$PHP" -d memory_limit=-1 "$COMPOSER" install --no-dev --prefer-dist \
                   --optimize-autoloader --no-interaction
        else
            echo "$(date '+%F %T')  WARN: composer.lock changed but composer not found at '$COMPOSER' — run 'composer install' by hand"
        fi
    fi

    "$PHP" artisan migrate --force

    # optimize:clear only — the app runs fine uncached; a bad config:cache would
    # white-screen the site until the next deploy. Turn on fuller caching later
    # by adding the lines below once the site is proven stable under it.
    "$PHP" artisan optimize:clear
    "$PHP" artisan storage:link 2>/dev/null || true
    "$PHP" artisan queue:restart 2>/dev/null || true
    # "$PHP" artisan config:cache && "$PHP" artisan route:cache && "$PHP" artisan view:cache

    echo "$(date '+%F %T')  done -> now at $(git rev-parse --short HEAD)"
}

main "$@"
