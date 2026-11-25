# Manabi 学び: Laravel LMS Demo

A small learning management system built with **Laravel 13, Blade and MySQL**, made as a public portfolio piece.
It is a demo with fictional courses, not client work.

**Live demo:** https://laravel-lms-demo-production.up.railway.app · One-click demo logins for admin, instructor and student on the login page
(`admin@demo.test`, `instructor@demo.test`, `student@demo.test`, password `demo1234`).

## What it shows

| Area | Details |
| --- | --- |
| Roles | Admin, instructor and student. Policies and a `manage-course` gate: instructors can only edit their own courses, only admins manage users. |
| Learning | Course catalog, enrolment, ordered lessons, per-lesson completion, a server-graded quiz with a pass score, progress bar (lessons 90 %, passed quiz 10 %). |
| Data integrity | Unique indexes on enrolments and lesson completions, `firstOrCreate` / `insertOrIgnore` so double clicks never create duplicates; quiz answers are graded on the server only. |
| Admin panel | Hand-built (no package): dashboard with stats, course CRUD with draft/publish, lesson and question editors, per-course student progress, user role management. |
| Caching | Versioned catalog cache: model events bump a version key, so no stale pages and no cache-tag dependency. Unserialisation is limited to an allow-list of classes. |
| i18n | English, Japanese and Vietnamese for the UI (`lang/*.json`) and for content (JSON columns per locale). |
| Performance | `/performance` page shows the Locust scenario and the latest load-test results (`loadtest/results.json`). |
| Tests | Feature tests for roles and access, enrolment and progress, quiz grading, admin CRUD, caching, security headers and error pages. |

## Run locally

```bash
composer install && npm install && npm run build
cp .env.example .env && php artisan key:generate
php artisan demo:install
php artisan serve
php artisan test
```

## Load test

```bash
pip install locust
locust -f loadtest/locustfile.py --host https://<domain> \
  --users 500 --spawn-rate 25 --run-time 5m --headless --csv loadtest/run500
```

Summarise the CSV into `loadtest/results.json` to show it on the `/performance` page.

## Deploy on Railway

1. New service → GitHub repo → this repository. Add a **MySQL** database.
2. Set the variables from `.env.example.railway` on the web service.
3. Settings → Networking → Generate domain (port 8080). Pre-deploy command: `php artisan demo:install`.

## Security notes

- `APP_DEBUG` is forced off unless `APP_ENV` is `local` or `testing`; custom error pages never show stack traces.
- Strict CSP (no inline scripts), `X-Frame-Options: DENY`, `nosniff`, HSTS, `Referrer-Policy`, `Permissions-Policy`; `X-Powered-By` removed.
- Secure, HTTP-only session cookies in production, CSRF on every form, rate limits on login, registration and quiz submits.
- No secrets in the repository: keys live only in the host's environment variables.
