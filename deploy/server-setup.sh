#!/bin/bash
# =============================================================================
#  ONE-TIME live-server setup for way2party auto-deploy
# -----------------------------------------------------------------------------
#  Run this ON THE LIVE SERVER over SSH, from the Laravel root — the folder that
#  contains `artisan` and whose `public/` directory is served as way2party.com:
#
#      cd /home/YOURUSER/path-to-laravel-root
#      bash deploy/server-setup.sh
#
#  What it does (all safe / idempotent — re-runnable):
#    1. verifies you are in the Laravel root
#    2. finds the php + composer binaries for this cPanel account
#    3. takes a full backup tarball in your home dir
#    4. generates an SSH deploy key and asks you to add it to GitHub (read-only)
#    5. turns this directory into a git checkout of origin/main WITHOUT
#       overwriting a single file yet
#    6. shows `git status` so you can spot hand-edits made on the server
#    7. writes deploy/.deploy-env (php/composer paths) for server-pull.sh
#    8. optionally runs the first real deploy
#
#  It NEVER touches: .env, storage/, public/images/upload/, Modules/,
#  public/modules/, bootstrap/cache/, or the server's Passport keys.
# =============================================================================
set -uo pipefail

G=$'\e[32m'; Y=$'\e[33m'; R=$'\e[31m'; N=$'\e[0m'
say()  { echo "${G}==>${N} $*"; }
warn() { echo "${Y}!! ${N} $*"; }
die()  { echo "${R}XX ${N} $*" >&2; exit 1; }
rule() { echo "${Y}────────────────────────────────────────────────────────────────${N}"; }

REPO_SSH="git@github.com:amitpableinventurs-prog/way2party.git"
BRANCH="main"

# --- 1. sanity: are we in the Laravel root? ---------------------------------
[ -f artisan ] || die "No 'artisan' file here. cd into the Laravel root first, then re-run."
[ -d public ] || die "No 'public/' dir here — this does not look like the Laravel root."
APP_DIR="$(pwd -P)"
say "Laravel root : $APP_DIR"
command -v git >/dev/null || die "git is not installed / not in PATH on this server."

# --- 2. locate php + composer for this account ----------------------------
PHP_BIN=""
for c in "$(command -v php || true)" \
         /opt/cpanel/ea-php83/root/usr/bin/php \
         /opt/cpanel/ea-php82/root/usr/bin/php \
         /usr/local/bin/php /usr/bin/php ; do
  [ -n "$c" ] && [ -x "$c" ] && { PHP_BIN="$c"; break; }
done
[ -n "$PHP_BIN" ] || die "php binary not found — edit deploy/.deploy-env by hand afterwards."
say "PHP          : $PHP_BIN  ($("$PHP_BIN" -r 'echo PHP_VERSION;' 2>/dev/null))"

COMPOSER_BIN=""
for c in /opt/cpanel/composer/bin/composer \
         "$HOME/composer.phar" "$HOME/bin/composer" \
         "$(command -v composer || true)" ; do
  [ -n "$c" ] && { [ -x "$c" ] || [ -f "$c" ]; } && { COMPOSER_BIN="$c"; break; }
done
if [ -n "$COMPOSER_BIN" ]; then
  say "Composer     : $COMPOSER_BIN"
else
  COMPOSER_BIN="/opt/cpanel/composer/bin/composer"
  warn "composer not found — guessing $COMPOSER_BIN . Fix deploy/.deploy-env if the first deploy fails."
fi

# --- 3. backup -----------------------------------------------------------
BK="$HOME/way2party-backup-$(date +%F-%H%M%S).tar.gz"
say "Backup       : $BK   (may take a minute…)"
tar czf "$BK" --exclude=node_modules --exclude=vendor --exclude=.git . 2>/dev/null \
  || warn "tar reported warnings (usually harmless permission notes)"

# --- 4. deploy key -----------------------------------------------------
KEY="$HOME/.ssh/id_ed25519"
mkdir -p "$HOME/.ssh"; chmod 700 "$HOME/.ssh"
if [ ! -f "$KEY" ]; then
  ssh-keygen -t ed25519 -N '' -C "way2party-deploy-$(date +%F)" -f "$KEY" >/dev/null
  say "Generated a new deploy key: $KEY"
else
  say "Re-using existing key: $KEY"
fi
grep -q '^github.com ' "$HOME/.ssh/known_hosts" 2>/dev/null \
  || ssh-keyscan -t rsa,ed25519 github.com >> "$HOME/.ssh/known_hosts" 2>/dev/null

echo; rule
echo "${Y} ACTION NEEDED — add this PUBLIC key to GitHub as a DEPLOY KEY:${N}"
echo "${Y}   repo  ->  Settings  ->  Deploy keys  ->  Add deploy key${N}"
echo "${Y}   Title: live-server    |    leave 'Allow write access' UNCHECKED${N}"
rule
cat "$KEY.pub"
rule
read -rp "Done on GitHub? press Enter to continue (Ctrl+C to abort) " _

if ssh -o BatchMode=yes -o StrictHostKeyChecking=accept-new -T git@github.com 2>&1 \
     | grep -q 'successfully authenticated'; then
  say "GitHub SSH auth OK."
else
  warn "GitHub has not confirmed the key yet — if the fetch below fails, wait a minute and re-run."
fi

# --- 5. adopt this directory as a git checkout of origin/main -----------
if [ ! -d .git ]; then
  git init -q
  git symbolic-ref HEAD "refs/heads/$BRANCH"
  say "git init (fresh repo)"
fi
git remote remove origin 2>/dev/null || true
git remote add origin "$REPO_SSH"
say "Fetching origin/$BRANCH …"
git fetch -q origin "$BRANCH" || die "git fetch failed — deploy key not active. Re-check step 4."
git reset -q "origin/$BRANCH"                       # HEAD + index = GitHub; working files untouched
git branch --set-upstream-to="origin/$BRANCH" "$BRANCH" 2>/dev/null || true
say "Directory is now a checkout of origin/$BRANCH (no files changed yet)."

# --- 6. did anyone hand-edit real source on the server? ----------------
SRC_DIFF="$(git diff --name-only -- app config routes database resources bootstrap \
             composer.json composer.lock 2>/dev/null || true)"
echo; say "git status (review this):"
git status --short | head -50
echo
if [ -n "$SRC_DIFF" ]; then
  rule
  warn "These TRACKED source files differ on the server vs GitHub:"
  echo "$SRC_DIFF" | sed 's/^/    /'
  warn "STOP. Nothing has been overwritten. Copy these edits out, commit them to"
  warn "GitHub from your PC, push, then re-run this script."
  rule
  exit 2
fi
say "No unexpected source edits — safe to proceed."

# --- 7. write server-specific env for server-pull.sh ------------------
cat > deploy/.deploy-env <<EOF
# generated by deploy/server-setup.sh on $(date)
# server-specific binary paths — this file is git-ignored, never deployed
PHP="$PHP_BIN"
COMPOSER="$COMPOSER_BIN"
BRANCH="$BRANCH"
EOF
say "Wrote deploy/.deploy-env"

# --- 8. first deploy ---------------------------------------------------
echo; rule
echo "Ready for the first deploy. This will:"
echo "  git checkout -- .   (take GitHub's version of every tracked file)"
echo "  composer install --no-dev   +   artisan migrate --force   +   cache rebuild"
rule
read -rp "Run the first deploy now? [y/N] " ans
if [ "${ans,,}" = "y" ]; then
  git checkout -- .
  bash deploy/server-pull.sh || die "first deploy failed — read the output above."
  say "First deploy done. Open way2party.com and check login + an organizer page."
else
  warn "Skipped. Run it yourself when ready:  git checkout -- . && bash deploy/server-pull.sh"
fi

echo; rule
echo "${G} LAST STEP — add the cron job:${N}  cPanel  ->  Cron Jobs  ->  every 2 minutes:"
echo
echo "   */2 * * * * $APP_DIR/deploy/server-pull.sh >> $APP_DIR/storage/logs/deploy.log 2>&1"
echo
echo " After that: every  git push  from your PC is live within ~2-3 minutes."
rule
