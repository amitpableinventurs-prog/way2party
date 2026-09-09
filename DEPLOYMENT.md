# Deployment — local → GitHub → way2party.com

No cPanel API token needed. The live server pulls from GitHub itself (cron), and
GitHub Actions *also* SSHes in for an instant deploy when it can. Either path alone
delivers every push — together they are belt **and** braces.

```
  your PC:  git push
     │
     ├─────────────► GitHub Actions  (.github/workflows/deploy.yml)
     │                 ssh way2party@HOST  ->  bash deploy/server-pull.sh      (~30 s, if enabled)
     │
     └─────────────► live server cron  (every 2 min)
                       bash deploy/server-pull.sh                              (≤ 2 min, always)
                          │  new commit on origin/main?
                          │    git reset --hard origin/main   (.env, storage/, Modules/, uploads kept)
                          │    composer install --no-dev
                          │    php artisan migrate --force
                          │    optimize:clear + config/route/view/event cache + storage:link
                          ▼
                       way2party.com updated
```

`server-pull.sh` takes a `flock`, so the cron run and the SSH run can never collide.

**Front-end assets are NOT built by the pipeline.** Whatever is committed under
`public/js`, `public/css`, `public/mix-manifest.json` ships as-is. If you touch
anything in `resources/js` or `resources/css`:

```bash
npm ci          # first time only
npm run prod
git add public/js public/css public/mix-manifest.json && git commit && git push
```

`.github/workflows/ci.yml` runs on every push/PR: composer install, `php -l` lint,
and an asset compile check (a broken build is caught even though it isn't shipped).

---

## One-time setup

### A. Your PC — make `git push` work permanently

Repo: `https://github.com/amitpableinventurs-prog/way2party`. Sign in **once**:

```bash
gh auth login          # GitHub.com → HTTPS → "Login with a web browser"
gh auth setup-git
git push origin main    # must say "Everything up-to-date" — no auth error
```

Your GitHub account needs **write access** to that repo (owner or collaborator).
Windows, if a stale token is stuck: clear it first —
`printf "protocol=https\nhost=github.com\n\n" | git credential-manager erase`

### B. Live server — run the setup script (cPanel → **Terminal**)

Open cPanel → **Terminal**. Go to the Laravel root — the folder with `artisan`
whose `public/` is served as way2party.com — and get the setup script:

```bash
cd ~                                  # then find the Laravel root:
find ~ -maxdepth 4 -name artisan -not -path '*/vendor/*'
cd /home/way2party/…                  # the directory that printed

# fetch just the setup script (repo is private → use the deploy key flow inside it;
# for now paste it in with nano, OR if the dir is already a git repo:)
mkdir -p deploy
nano deploy/server-setup.sh           # paste the file contents, Ctrl+O, Ctrl+X

bash deploy/server-setup.sh
```

The script is safe and re-runnable. It:

1. finds this account's `php` + `composer` binaries
2. takes a full backup tarball in `~/`
3. generates an SSH **deploy key** and prints the public half — **you paste it into
   GitHub**: repo → Settings → Deploy keys → Add deploy key → *leave write access
   OFF* → Save, then press Enter in the terminal
4. turns the directory into a git checkout of `origin/main` **without changing a
   single file yet**, then shows `git status`
5. if anyone hand-edited tracked source (`app/ config/ routes/ …`) on the server it
   **stops** — nothing overwritten. Copy those edits out, commit from your PC, push,
   re-run.
6. writes `deploy/.deploy-env` (php/composer paths — git-ignored, never deployed)
7. offers to run the first real deploy, and prints the exact cron line for step C

**A deploy keeps vs replaces:**

| Kept — untracked / git-ignored | Replaced with the GitHub version |
|---|---|
| `.env` | `app/ config/ routes/ resources/ database/ bootstrap/app.php` |
| `storage/app/**`, `storage/logs/**` | compiled `public/js`, `public/css`, `mix-manifest.json` |
| `public/images/upload/**`, `public/storage` | `.htaccess` (root + `public/`) |
| `Modules/**`, `public/modules/**` | `composer.json` / `composer.lock` |
| `bootstrap/cache/*.php` | `artisan`, `deploy/server-pull.sh`, `deploy/server-setup.sh` |
| `storage/oauth-*.key` (server keeps its own Passport keys) | |
| `deploy/.deploy-env` | |

### C. Live server — the cron backbone (cPanel → **Cron Jobs**)

Every 2 minutes — `server-setup.sh` prints this line with your real paths filled in:

```
*/2 * * * * /home/way2party/…/deploy/server-pull.sh >> /home/way2party/…/storage/logs/deploy.log 2>&1
```

This alone makes every `git push` go live within 2 minutes. Everything below is
just to make it *faster*.

### D. (Optional) Instant deploy — GitHub Actions SSH job

Skips the ≤2 min wait; the cron stays as the safety net.

1. Generate a CI keypair and authorise it on the server (cPanel Terminal):
   ```bash
   ssh-keygen -t ed25519 -f ~/way2party_ci -N ''
   cat ~/way2party_ci.pub >> ~/.ssh/authorized_keys
   cat ~/way2party_ci                       # copy this whole block → SSH_KEY secret
   rm ~/way2party_ci ~/way2party_ci.pub
   ```
2. Repo → **Settings → Secrets and variables → Actions → Secrets** tab:

   | Secret | Value |
   |---|---|
   | `SSH_HOST` | `3.0.159.67` |
   | `SSH_USER` | `way2party` |
   | `SSH_PORT` | `22` |
   | `SSH_KEY` | the **private** key from step 1 |
   | `DEPLOY_PATH` | the Laravel root, e.g. `/home/way2party/public_html` |

3. **Variables** tab → add `DEPLOY_ENABLED` = `true`.

If the Actions log shows `ssh: handshake failed` the host firewall blocks GitHub's
runners — set `DEPLOY_ENABLED` back to `false`, the cron still delivers.

### E. Confirm the domain

Domain **Document Root** = `<Laravel root>/public`, and a real `.env` exists on the
server (it does — the site already runs).

---

## Day-to-day

```bash
git add -A && git commit -m "…" && git push
# changed resources/js|css? also:  npm run prod && git add public/js public/css public/mix-manifest.json && git commit
```

Live in ≤2 min (≈30 s if the SSH job is on). Watch it land:

```bash
tail -f storage/logs/deploy.log      # cPanel Terminal, on the server
```

## Rollback

```bash
git revert <bad-sha> && git push     # redeploys the revert automatically
```

Or on the server: `git reset --hard <good-sha>` then `bash deploy/server-pull.sh`.
`optimize:clear` runs first every deploy, so a broken config cache never survives one.

---

## Troubleshooting

| Symptom | Fix |
|---|---|
| `git push` → `Invalid username or token` | redo step A: `gh auth login` + `gh auth setup-git` |
| `deploy.log`: `ABORT: uncommitted changes` | someone edited tracked files on the server — `git status`, then commit them to GitHub or `git checkout -- <path>` to discard |
| `deploy.log` / setup: `Permission denied (publickey)` on fetch | server **deploy key** (step B.3) not added to GitHub, or the remote is HTTPS — `git remote set-url origin git@github.com:amitpableinventurs-prog/way2party.git` |
| `server-setup.sh`: composer not found | edit `deploy/.deploy-env`, set `COMPOSER=` to the real path (`~/composer.phar` or `/opt/cpanel/composer/bin/composer`) |
| Actions `deploy`: `ssh: handshake failed` | wrong `SSH_KEY` / literal `\n` in it / public half not in server `~/.ssh/authorized_keys` / firewall blocks GitHub runners — set `DEPLOY_ENABLED=false`, rely on cron |
| Actions `deploy` skipped every run | repo variable `DEPLOY_ENABLED` is not `true` |
| White screen after a deploy | `php artisan optimize:clear`. If it persists a package calls `env()` outside `config/*` — drop `config:cache` then `route:cache` from `server-pull.sh` |
| Assets 404 / stale | you changed `resources/js\|css` but didn't `npm run prod` + commit `public/js\|css\|mix-manifest.json` |
| `Class "Modules\…" not found` | `Modules/` + `public/modules/` are git-ignored — deploy modules separately or un-ignore them |
| API / mobile login breaks after first deploy | restore `storage/oauth-private.key` + `storage/oauth-public.key` from the backup tarball, then `php artisan config:clear` |

## cPanel Git Version Control (third fallback)

Register the repo dir under cPanel → **Git Version Control** and its **Deploy HEAD
Commit** button runs [`.cpanel.yml`](.cpanel.yml). Keep its `PHP=` / `COMPOSER=`
lines in sync with `deploy/.deploy-env`.
