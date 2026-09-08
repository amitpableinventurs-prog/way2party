# Deployment — GitHub → cPanel (Git Version Control)

Every push to `main` runs this pipeline:

```
push to main
   │
   ▼
GitHub Actions  (.github/workflows/deploy.yml)
   │  1. npm ci && npm run prod        → build public/js, public/css, mix-manifest.json
   │  2. commit compiled assets back to main   ([skip ci], won't re-trigger)
   │  3. call cPanel UAPI  VersionControlDeployment::create
   ▼
cPanel  pulls HEAD  →  runs .cpanel.yml on the live server
   │  composer install --no-dev
   │  php artisan migrate --force
   │  php artisan optimize:clear + config/route/view/event cache
   │  storage:link, passport:keys (only if missing)
   ▼
Live site updated
```

`.github/workflows/ci.yml` additionally runs on every PR: composer validate, dependency
install, `php -l` lint, an `artisan` boot check, and an asset build.

---

## One-time setup

### 0. Server is ALREADY live (way2party.com) — connect the existing install

The production code already exists on the server (installed, real `.env`, real DB,
uploads in place). Do **not** wipe it — turn that directory into a git checkout of
`origin/main` so future pushes deploy on top.

**What git will and won't touch** on first checkout:

| Safe (git-ignored / untracked) | Overwritten by the checkout |
|---|---|
| `.env` | source code (`app/`, `config/`, `routes/`, `resources/`, `database/`) |
| `storage/app/**` uploads, `storage/logs/**` | compiled assets `public/js`, `public/css`, `mix-manifest.json` |
| `public/images/upload/**`, `public/storage` | `.htaccess` (root + `public/`) |
| `Modules/**`, `public/modules/**` | `composer.json` / `composer.lock` |
| `bootstrap/cache/*.php` | |
| `storage/oauth-*.key` *(now git-ignored — server keeps its own)* | |

Steps (cPanel → **Terminal**, or SSH):

```bash
cd ~/path/to/live/app          # the dir whose /public is the way2party.com docroot
php artisan down

# snapshot first — safety net
tar czf ~/way2party-backup-$(date +%F).tar.gz --exclude=node_modules --exclude=vendor .

git init
git remote add origin https://github.com/amitpableinventurs-prog/way2party.git
git fetch origin
git checkout -f -b main origin/main      # keeps .env, storage, uploads, Modules (all ignored)

composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link || true
php artisan up
```

Open way2party.com and confirm it still works (log in, load an organizer page). If the
mobile apps / API log users out, the Passport keys differ — that only happens if the old
keys were lost; restore `storage/oauth-*.key` from the backup.

Then register this same directory in cPanel Git Version Control:

cPanel → **Git™ Version Control** → **Create** → *Clone a Repository* **OFF** → set
**Repository Path** to this existing directory (cPanel detects the `.git` you just made).
Now skip to step **4** below (`.cpanel.yml` paths) and step **5** (GitHub secrets).

If you would rather deploy into a **fresh** directory and cut the docroot over once it is
verified, use steps 1–6 instead and copy `.env` + `storage/` + `Modules/` across first.

---

### 1. cPanel — create the repository

cPanel → **Git™ Version Control** → **Create**

| Field | Value |
|---|---|
| Clone URL | `https://github.com/amitpableinventurs-prog/way2party.git` |
| Repository Path | e.g. `/home/CPUSER/repositories/way2party` |

Private repo → cPanel needs read access to GitHub. Either:

- **Deploy key (recommended):** `cat ~/.ssh/id_*.pub` on the server (or generate with
  `ssh-keygen -t ed25519`), add it in GitHub → repo → *Settings → Deploy keys* (read-only),
  and use the SSH clone URL `git@github.com:amitpableinventurs-prog/way2party.git`; **or**
- **PAT in the URL:** `https://USERNAME:GITHUB_PAT@github.com/amitpableinventurs-prog/way2party.git`

### 2. cPanel — point the domain at `public/`

The Laravel front controller is in `public/`. Set the domain / subdomain **Document Root** to:

```
/home/CPUSER/repositories/way2party/public
```

(cPanel → *Domains* → edit → Document Root.) If your host forbids a docroot outside
`public_html`, instead clone into `public_html` itself and set docroot to
`public_html/public`, **or** use the `rsync` block at the bottom of `.cpanel.yml`.

### 3. Live server — create `.env` (once)

`.env` is **not** in git. On the server, in the repo root:

```bash
cp .env.example .env
/usr/local/bin/php artisan key:generate
```

Then edit `.env`: set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://yourdomain`,
and the `DB_*` / `MAIL_*` / payment credentials. (Or run the web installer at
`https://yourdomain/installer` once, which writes these for you.)

### 4. Fix the paths in `.cpanel.yml`

Confirm on the server via SSH:

```bash
which php            # → PHP=...    (or /opt/cpanel/ea-php83/root/usr/bin/php)
which composer        # → COMPOSER=... (often /opt/cpanel/composer/bin/composer)
```

Edit the two `export` lines at the top of [`.cpanel.yml`](.cpanel.yml) and commit.

### 5. GitHub — add repository secrets

Repo → *Settings → Secrets and variables → Actions → New repository secret*:

| Secret | Example | Where to get it |
|---|---|---|
| `CPANEL_HOST` | `server201.web-hosting.com` | cPanel sidebar "Server Information" — hostname only, no `https://`, no `:2083` |
| `CPANEL_USER` | `cpuser` | cPanel account username |
| `CPANEL_API_TOKEN` | `ABC123...` | cPanel → **Manage API Tokens** → Create |
| `CPANEL_REPO_ROOT` | `/home/cpuser/repositories/way2party` | the Repository Path from step 1 |

> If `2083` is blocked for outbound from GitHub runners, deployment step will time out.
> Fallback: skip the trigger job and enable **cPanel → Git Version Control → Pull on push**
> via a GitHub webhook, or click **Deploy HEAD Commit** manually.

### 6. First deploy

```bash
git push -u origin main
```

Watch it in the repo's **Actions** tab, then verify the site. First deploy also needs the
DB migrated — `.cpanel.yml` does that automatically with `migrate --force`.

---

## Rollback

cPanel → Git Version Control → **Manage** → *Pull or Deploy* shows commit history.
Check out an older commit locally, `git revert`, and push — or in cPanel deploy a
previous commit. `optimize:clear` runs at the start of every deploy, so a bad config
cache never survives a re-deploy.

## Troubleshooting

| Symptom | Fix |
|---|---|
| White screen after deploy | SSH in, `php artisan optimize:clear`. If it stays broken, a package calls `env()` outside `config/*` — remove `config:cache` (and maybe `route:cache`) from `.cpanel.yml`. |
| `498 / could not read Username` in cPanel pull | Deploy key not added to GitHub, or wrong (HTTPS vs SSH) clone URL. |
| Actions deploy job: `cPanel API error` | Wrong `CPANEL_*` secret, expired API token, or `:2083` blocked. |
| Assets 404 | `npm run prod` failed in Actions — check the build job log; confirm `public/mix-manifest.json` got committed. |
| `Class "Modules\..." not found` | `/Modules` and `public/modules` are git-ignored. Commit needed modules or deploy them separately. |
| Passport / login broken | `storage/oauth-*.key` mismatch. Delete both on the server and run `php artisan passport:keys`, then re-issue clients. |
