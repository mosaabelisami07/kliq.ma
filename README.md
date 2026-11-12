# KLIQ — Agency Website & Admin Dashboard

Laravel 13 · Blade · Tailwind CSS 4 · Vite · MySQL (SQLite works for local development).

- **Public site** (`/en`, `/fr`, `/ar`, with right-to-left layout for Arabic): one-page agency site (Hero, About, Services, Our Work, Process, Why KLIQ, Contact), plus project, project-archive and service detail pages.
- **Admin dashboard** (`/admin`): inquiries, projects and categories, services, website content, settings, profile.

---

## 1. Install

```bash
composer install
npm install
cp .env.example .env        # Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate
```

## 2. Configure `.env`

| Key | What to set |
| --- | --- |
| `APP_URL` | The exact URL the site is served from (`http://localhost:8000` locally, `https://your-domain` in production). Image URLs are built from it. |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Your MySQL database. Create the database first: `CREATE DATABASE kliq CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;` (For quick local work you can use `DB_CONNECTION=sqlite` instead.) |
| `ADMIN_ACCESS_CODE` | Optional second secret asked on the admin login page. See section 5. |
| `MAIL_*` | Mail server used for new-inquiry notifications. See section 6. |
| `KLIQ_NOTIFICATION_EMAIL` | Fallback address for new-inquiry emails (the dashboard setting takes priority). |
| `MEDIA_DISK` | `public` (default) or `s3` for uploaded images. |

Secrets live only in `.env` and are read server-side through `config/kliq.php`. Never give a secret a `VITE_` prefix: those values are bundled into public JavaScript.

## 3. Database & starting content

```bash
php artisan migrate
php artisan db:seed
```

The seeder adds 4 project categories, 9 services and the 6 portfolio projects with their real photos (copied from `database/seeders/images` into storage). It is safe to run again; existing records are not overwritten.

## 4. Image storage

Uploaded images are stored on the `public` disk (`storage/app/public`) and served through a symbolic link:

```bash
php artisan storage:link
```

- Uploads accept **JPG, PNG and WebP only** (both the extension and the actual file type are checked; SVG and anything executable are rejected). Max 5 MB, files are renamed randomly.
- If a file is missing, the site shows the brand's generated orange artwork instead of a broken image.
- **Adding or replacing images:** use the dashboard (Projects → edit → Thumbnail / Gallery; Services → Image; Settings → Logo, Favicon, Social sharing image). Static assets that ship with the code (favicons, `og-image.png`) are in `public/` and are referenced with `asset()`.
- To use S3 instead: set `MEDIA_DISK=s3` and the `AWS_*` keys; the bucket must allow public reads of uploaded files.

## 5. Create the first administrator

```bash
php artisan kliq:admin
```

You will be asked for an email, a name and a password (at least 12 characters with letters and numbers, typed at a hidden prompt, so it never appears in shell history). Running the command again for an existing email resets that admin's password. No credentials are stored in the code or seeders.

**Optional access code** (a second secret on the login form):

```bash
php artisan kliq:hash-code
```

Paste the printed `ADMIN_ACCESS_CODE='$2y$…'` line into `.env` (single quotes are required for hashes). A plain value also works, but the hash is safer.

Login security: CSRF protection, session regeneration, 5 failed attempts per email/IP → 5-minute lockout, identical error message for every failure, admin pages sent with `no-store` and `noindex` headers.

## 6. Email notifications for new inquiries

1. Set mail delivery in `.env`, for example:
   ```dotenv
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.your-provider.com
   MAIL_PORT=587
   MAIL_USERNAME=your-user
   MAIL_PASSWORD=your-password
   MAIL_FROM_ADDRESS="website@your-domain"
   MAIL_FROM_NAME="KLIQ Website"
   ```
   With `MAIL_MAILER=log` (the default) emails are written to `storage/logs/laravel.log` instead of being sent.
2. In the dashboard → **Settings → Inquiry notifications**, enter the notification email and keep the switch on.

Every inquiry is saved to the database first; a mail failure is logged and never loses the inquiry.

## 7. Run locally

```bash
composer run dev
```

This runs the project's development processes together. If you prefer, run them in two terminals:

```bash
php artisan serve
npm run dev
```

Open http://localhost:8000 (redirects to `/en`, `/fr` or `/ar` from the browser language) and http://localhost:8000/admin.

## 8. Test it

```bash
php artisan test
```

Manual checks:

1. **Contact form:** on the home page, submit the form under "Contact". Try an invalid email first (inline error), then a valid message: the button shows "Sending…", then a thank-you panel appears. Submissions sent within 3 seconds of loading the page are rejected as bots, and only 3 are accepted per minute per IP.
2. **Dashboard:** sign in at `/admin`; the new inquiry is highlighted with an unread badge. Open it (marks it read), change the status, add an internal note, use "Reply by email", export the CSV, delete it.
3. **Content:** edit a project (upload a thumbnail, add gallery images, unpublish), reorder services, change the hero heading in *Website content*, add your email/phone/social links in *Settings*, then refresh the public site.

## 9. Deploy to production

1. Server: PHP 8.3+ with `pdo_mysql`, `fileinfo`, `gd`, `mbstring`; MySQL 8 (or MariaDB 10.6+); the web root must point to `public/`.
2. `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`, `SESSION_SECURE_COOKIE=true`, real `DB_*` and `MAIL_*` values, and a fresh `APP_KEY` (`php artisan key:generate` once, on the first deploy only).
3. Deploy:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan migrate --force
   php artisan db:seed --force        # first deploy only
   php artisan storage:link
   php artisan optimize               # caches config, routes, views
   php artisan kliq:admin             # first deploy only
   ```
4. Make `storage/` and `bootstrap/cache/` writable by the web server user.
5. Serve the site over HTTPS only.
6. Optional: add `Sitemap: https://your-domain/sitemap.xml` to `public/robots.txt`.

After any later `.env` change, run `php artisan optimize` again.

## Where things live

| Area | Location |
| --- | --- |
| Public controllers | `app/Http/Controllers/{Home,Project,Service,Inquiry,Sitemap}Controller.php` |
| Admin controllers | `app/Http/Controllers/Admin/` |
| Validation | `app/Http/Requests/ContactRequest.php`, `app/Http/Requests/Admin/` |
| Editable settings & defaults | `app/Support/SiteSettings.php`, `config/kliq.php`, `lang/{en,fr,ar}/site.php` (`defaults`) |
| Routes | `routes/web.php` (public), `routes/admin.php` (dashboard) |
| Views | `resources/views/pages`, `resources/views/admin`, `resources/views/components` |
| Styles / scripts | `resources/css/app.css` + `resources/js/app.js` (site), `resources/css/admin.css` + `resources/js/admin.js` (dashboard) |
# kliq.ma
# kliq.ma
