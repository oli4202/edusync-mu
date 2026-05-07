# Deploy EduSync MU to InfinityFree

This guide is tailored for this repository and keeps secrets out of uploads.

## 1) Create hosting + database

1. Sign in to InfinityFree and create a free hosting account.
2. Open the hosting account control panel.
3. Go to **MySQL Databases** and create:
   - Database name
   - Database user
   - Database password
4. Copy the exact DB values shown by InfinityFree:
   - `DB_HOST` (usually starts with `sql...`)
   - `DB_NAME` (usually starts with `if0_...`)
   - `DB_USER`
   - `DB_PASS`

## 2) Import database schema

1. Open **phpMyAdmin** from InfinityFree control panel.
2. Select your new database.
3. Import `sql/schema.sql`.

## 3) Prepare `.env` safely

1. On your local machine, copy `.env.infinityfree.example` to `.env`.
2. Fill `.env` with your real InfinityFree DB values.
3. Set strong values for:
   - `DB_PASS`
   - `FACULTY_SECRET`
4. Keep `APP_ENV=production` and `APP_DEBUG=false`.

## 4) Build upload zip (PowerShell)

From project root, run:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\build-infinityfree-zip.ps1
```

This creates: `release/edusync-infinityfree-upload.zip`

## 5) Upload project

1. Open InfinityFree **Online File Manager**.
2. Go to `htdocs/`.
3. Upload and extract `edusync-infinityfree-upload.zip`.
4. Ensure app entry is exactly:
   - `htdocs/index.php`
   - `htdocs/.htaccess`
   - `htdocs/app/`, `htdocs/config/`, `htdocs/sql/`, etc.

Do not keep an extra nested folder (like `htdocs/edusync-mu-complete/index.php`).

## 6) Final checks

1. Open your domain/subdomain.
2. Test:
   - Signup/login
   - Dashboard
   - Fees
   - Question bank
3. Log in to admin and immediately change default admin password.

## Troubleshooting

- 500 error: verify `.env` values and schema import.
- DB connection error: wrong `DB_HOST` (use InfinityFree-provided host, not localhost).
- Route 404: make sure `htdocs/.htaccess` exists.
- Missing styles/scripts: confirm `assets/` uploaded.
