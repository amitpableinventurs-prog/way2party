# Deployment — GitHub → live server (way2party.com)

No cPanel API token needed. The live server pulls from GitHub itself on a cron.

```
push to main
   │
   ▼
GitHub Actions  (.github/workflows/build-assets.yml)
   │  npm ci && npm run prod  →  compile public/js, public/css, mix-manifest.json
   │  commit the built files back to main   ([skip ci] — no loop)
   ▼
main branch now has code + built assets
   │
   ▼
Live server cron, every 5 min  (deploy/server-pull.sh)
   │  new commit on origin/main?  no → exit
   │  yes ↓
   │  git reset --hard origin/main         (.env, storage/, Modules/, uploads untouched)
   │  composer install --no-dev
   │  php artisan migrate --force
   │  php artisan optimize:clear + config/route/view/event cache + storage:link
   ▼
Live site updated
```

`.github/workflows/ci.yml` runs on every PR: composer validate, install, `php -l` lint,
an `artisan` boot check, and an asset build.

---

## One-time setup (SSH)

### 1. Connect the existing live install to the repo

The code already exists on the server (installed, real `.env`, real DB, uploads). Don't
wipe it — turn that directory into a git checkout of `origin/main`.

**What a checkout touches — and doesn't:**

| Safe — git-ignored / untracked | Replaced with the GitHub version |
|---|---|
| `.env` | source: `app/ config/ routes/ resources/ database/ bootstrap/app.php` |
| `storage/app/**`, `storage/logs/**` | compiled `public/js`, `public/css`, `mix-manifest.json` |
| `public/images/upload/**`, `public/storage` | `.htaccess` (root + `public/`) |
| `Modules/**`, `public/modules/**` | `composer.json` / `composer.lock` |
| `bootstrap/cache/*.php` | `artisan`, `deploy/server-pull.sh` |
| `storage/oauth-*.key` (git-ignored — server keeps its own Passport keys) | |

```bash
# find the Laravel root (parent of the public/ that serves way2party.com)
find ~ -maxdepth 4 -name artisan -not -path '*/vendor/*'
cd /home/CPUSER/…            # that directory

php artisan down
tar czf ~/way2party-backup-$(date +%F-%H%M).tar.gz --exclude=node_modules --exclude=vendor .

# deploy key so the server can read the private repo
ls ~/.ssh/id_ed25519.pub 2>/dev/null || ssh-keygen -t ed25519 -N '' -f ~/.ssh/id_ed25519
cat ~/.ssh/id_ed25519.pub
#   → GitHub → repo → Settings → Deploy keys → Add deploy key   (read-only is enough)

# adopt the directory into git WITHOUT changing any file yet
git init
git branch -m main
git remote add origin git@github.com:amitpableinventurs-prog/way2party.git
git fetch origin
git reset origin/main        # HEAD + index = GitHub; working tree left exactly as-is
```

### 2. Diagnose — did anyone edit code directly on the server?

```bash
git status
git diff --stat
```

- **`modified:` under `app/ config/ routes/ resources/ database/`** → real server edits.
  `git diff -- <path>` to read them. Keep anything real: copy it aside, commit it to
  GitHub properly from your machine, then `git fetch origin` here again.
- **`modified:` only `public/js/**`, `public/css/**`, `mix-manifest.json`, `.htaccess`**
  → ignore; the GitHub version wins.
- **`untracked:` `.env`, `storage/…`, `Modules/…`, `public/images/upload/…`** → expected.
- **`deleted:`** → the server was missing files GitHub has.

### 3. Align the working tree to GitHub

Once `git status` shows nothing under `app/ config/ routes/ resources/ database/` that you
still need:

```bash
git checkout -- .
git branch --set-upstream-to=origin/main main

composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link || true
php artisan up
```

Open way2party.com — log in, open an organizer page, check the mobile-app / API login.
If API logins now fail, restore `storage/oauth-private.key` + `storage/oauth-public.key`
from the backup tarball and run `php artisan config:clear`.

### 4. Point `deploy/server-pull.sh` at your paths

```bash
which php        # e.g. /usr/local/bin/php   or  /opt/cpanel/ea-php83/root/usr/bin/php
which composer   # e.g. /opt/cpanel/composer/bin/composer
```

Either edit the `PHP=` / `COMPOSER=` defaults at the top of
[`deploy/server-pull.sh`](deploy/server-pull.sh) and push the change, or pass them in the
cron line (next step). `APP_DIR` auto-detects from the script's own location.

Test it by hand first:

```bash
bash deploy/server-pull.sh          # prints nothing & exits 0 when already up to date
```

### 5. Add the cron job

cPanel → **Cron Jobs** → add (every 5 minutes):

```
*/5 * * * * /home/CPUSER/way2party/deploy/server-pull.sh >> /home/CPUSER/way2party/storage/logs/deploy.log 2>&1
```

With explicit binaries if `which` above wasn't the default:

```
*/5 * * * * PHP=/opt/cpanel/ea-php83/root/usr/bin/php COMPOSER=/opt/cpanel/composer/bin/composer /home/CPUSER/way2party/deploy/server-pull.sh >> /home/CPUSER/way2party/storage/logs/deploy.log 2>&1
```

### 6. Verify end to end

Make a trivial change on `main` (from your machine), push, then within ~5 min:

```bash
tail -f /home/CPUSER/way2party/storage/logs/deploy.log
```

You should see `deploying xxxxxxx -> yyyyyyy … done`.

---

## Day-to-day

```bash
# on your machine
git add -A && git commit -m "…" && git push
```

Assets rebuild in GitHub Actions; the server pulls within 5 minutes. Nothing else to do.

## Rollback

```bash
# from your machine
git revert <bad-sha> && git push        # server rolls forward to the revert
```

Or on the server, pause the cron and `git reset --hard <good-sha>` + re-run the artisan
cache steps. `optimize:clear` runs at the start of every deploy, so a broken config cache
never survives the next pull.

## Instant deploys (optional)

The 5-minute cron delay is usually fine. For instant deploys, uncomment the `deploy:` job
at the bottom of [`build-assets.yml`](.github/workflows/build-assets.yml) — it SSHes in and
runs the same `server-pull.sh`. Needs `SSH_HOST` / `SSH_USER` / `SSH_KEY` / `SSH_PORT`
repo secrets (a keypair you create; public half in the server's `~/.ssh/authorized_keys`).
Keep the cron too, as a safety net.

## cPanel Git Version Control (alternative to cron)

If you prefer cPanel's UI: register the repo dir under cPanel → **Git™ Version Control**,
and it will run [`.cpanel.yml`](.cpanel.yml) whenever you click **Deploy HEAD Commit** (or
when triggered by its API). The cron approach above needs neither, so `.cpanel.yml` is just
a convenience/fallback — keep its `PHP=` / `COMPOSER=` lines in sync with the cron.

## Troubleshooting

| Symptom | Fix |
|---|---|
| `deploy.log`: `ABORT: uncommitted changes` | Someone edited files on the server. Commit them to GitHub (or `git checkout -- <path>` to discard), then it resumes. |
| `deploy.log`: `Permission denied (publickey)` | Deploy key not added to GitHub, or the remote is the HTTPS URL — `git remote set-url origin git@github.com:amitpableinventurs-prog/way2party.git`. |
| White screen after a deploy | `php artisan optimize:clear`. If it stays broken, a package calls `env()` outside `config/*` — drop `config:cache` (then `route:cache`) from `server-pull.sh`. |
| Assets 404 / stale | The Actions "Build assets" run failed — check its log; confirm `public/mix-manifest.json` was committed. |
| `Class "Modules\…" not found` | `/Modules` + `public/modules` are git-ignored — deploy modules separately or un-ignore them. |
| Cron didn't run | cPanel Cron Jobs needs the full path; check the account's cron email / `deploy.log` mtime. |
