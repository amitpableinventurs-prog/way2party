# Deployment — GitHub → live server (way2party.com)

No cPanel API token needed. GitHub Actions builds the assets and then SSHes into the
server to deploy.

```
push to main
   │
   ▼
GitHub Actions  (.github/workflows/deploy.yml)
   │  job build-assets:  npm ci && npm run prod  →  compile public/js, public/css,
   │                     mix-manifest.json,  commit back to main  ([skip ci] — no loop)
   │  job deploy:        ssh way2party@SSH_HOST  ->  bash deploy/server-pull.sh
   ▼
server-pull.sh on the live server
   │  git reset --hard origin/main         (.env, storage/, Modules/, uploads untouched)
   │  composer install --no-dev
   │  php artisan migrate --force
   │  php artisan optimize:clear + config/route/view/event cache + storage:link
   ▼
Live site updated  (~2–3 min after the push)
```

A cron running the same `deploy/server-pull.sh` every 5 min is an optional safety net
(see "Cron backup" below) in case an Actions run is skipped or fails.

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

Edit the `PHP=` / `COMPOSER=` defaults at the top of
[`deploy/server-pull.sh`](deploy/server-pull.sh) and push the change. `APP_DIR`
auto-detects from the script's own location.

Test it by hand:

```bash
bash deploy/server-pull.sh          # prints nothing & exits 0 when already up to date
```

### 5. GitHub — repo secrets + Actions permission

**Settings ▸ Actions ▸ General ▸ Workflow permissions** → **Read and write permissions** → Save.

**Settings ▸ Secrets and variables ▸ Actions ▸ `Secrets` tab** (the *Secrets* tab, **not**
Variables — `SSH_KEY` is a private key and must be masked):

| Secret | Value |
|---|---|
| `SSH_HOST` | `3.0.159.67` |
| `SSH_USER` | `way2party` |
| `SSH_PORT` | `22` |
| `SSH_KEY` | the **private** key (`-----BEGIN OPENSSH PRIVATE KEY-----` …) whose public half is in the server's `~/.ssh/authorized_keys` |
| `DEPLOY_PATH` | the Laravel root on the server, e.g. `/home/way2party/public_html` |

Generate the Actions keypair (on your machine or the server), then:

```bash
ssh-keygen -t ed25519 -f way2party_ci -N ''
cat way2party_ci.pub   >> ~/.ssh/authorized_keys   # ON THE SERVER
cat way2party_ci        # → paste as the SSH_KEY secret, then delete both local files
```

### 6. Verify end to end

Make a trivial change on `main`, push, then watch the repo **Actions** tab: `build-assets`
then `deploy` should both go green. Confirm on the server:

```bash
tail -n 20 storage/logs/deploy.log     # "deploying xxxxxxx -> yyyyyyy … done"
git -C "$DEPLOY_PATH" log -1 --oneline
```

### Cron backup (optional)

cPanel → **Cron Jobs**, every 5 min — runs the same script, so a missed/failed Actions
run still lands within 5 minutes:

```
*/5 * * * * /home/way2party/public_html/deploy/server-pull.sh >> /home/way2party/public_html/storage/logs/deploy.log 2>&1
```

---

## Day-to-day

```bash
# on your machine
git add -A && git commit -m "…" && git push
```

GitHub Actions builds the assets and deploys over SSH. Live in ~2–3 min. Nothing else to do.

## Rollback

```bash
# from your machine
git revert <bad-sha> && git push        # Actions redeploys the revert
```

Or on the server: `git reset --hard <good-sha>` + re-run the artisan cache steps.
`optimize:clear` runs at the start of every deploy, so a broken config cache never
survives the next deploy.

## cPanel Git Version Control (alternative to the SSH deploy)

If you prefer cPanel's UI: register the repo dir under cPanel → **Git™ Version Control**,
and it will run [`.cpanel.yml`](.cpanel.yml) whenever you click **Deploy HEAD Commit**. The
`deploy.yml` SSH job needs neither, so `.cpanel.yml` is just a convenience/fallback — keep
its `PHP=` / `COMPOSER=` lines in sync with `deploy/server-pull.sh`.

## Troubleshooting

| Symptom | Fix |
|---|---|
| Actions `deploy` job: `ssh: handshake failed` / `Permission denied (publickey)` | `SSH_KEY` secret is the wrong key / has literal `\n` / its public half isn't in the server's `~/.ssh/authorized_keys`; or the server firewall blocks GitHub runners on port 22. |
| Actions `deploy` job: `Host key verification failed` | first connection — the action accepts new host keys by default; if not, add `fingerprint` input or pre-seed `known_hosts`. |
| `deploy.log`: `ABORT: uncommitted changes` | Someone edited files on the server. Commit them to GitHub (or `git checkout -- <path>` to discard), then it resumes. |
| `deploy.log`: `Permission denied (publickey)` on `git fetch` | server's **deploy key** not added to GitHub, or the remote is the HTTPS URL — `git remote set-url origin git@github.com:amitpableinventurs-prog/way2party.git`. |
| `build-assets` job: `Permission denied` on `git push` | Settings ▸ Actions ▸ General ▸ Workflow permissions not set to **Read and write**. |
| White screen after a deploy | `php artisan optimize:clear`. If it stays broken, a package calls `env()` outside `config/*` — drop `config:cache` (then `route:cache`) from `server-pull.sh`. |
| Assets 404 / stale | The `build-assets` job failed — check its log; confirm `public/mix-manifest.json` was committed. |
| `Class "Modules\…" not found` | `/Modules` + `public/modules` are git-ignored — deploy modules separately or un-ignore them. |
