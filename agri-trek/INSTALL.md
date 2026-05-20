# 🛠️ Agri-Trek — Full Installation Guide
## Fixing "Internal Server Error" Step by Step

---

## ✅ Prerequisites

Make sure you have:
- **XAMPP / WAMP / Laragon** running with Apache + MySQL
- **PHP 8.2+** (check: `php -v` in terminal)
- **Composer** installed (check: `composer -V`)

---

## 📋 Step-by-Step Setup

### Step 1 — Extract the project
Extract `agri-trek-project.zip` into your web server folder:
- XAMPP → `C:/xampp/htdocs/agri-trek`
- WAMP  → `C:/wamp64/www/agri-trek`
- Laragon → `C:/laragon/www/agri-trek`

---

### Step 2 — Open terminal in project folder
```bash
cd C:/xampp/htdocs/agri-trek
```

---

### Step 3 — Install PHP dependencies
```bash
composer install
```
⚠️ This requires internet. It downloads ~30MB of Laravel packages.

---

### Step 4 — The .env file is ALREADY configured
The `.env` file is included with a pre-generated `APP_KEY`.
**You do NOT need to run `php artisan key:generate`.**

But you may edit DB credentials if needed:
```env
DB_DATABASE=agritrek
DB_USERNAME=root
DB_PASSWORD=        ← leave blank for XAMPP default
```

---

### Step 5 — Create the database
Open **phpMyAdmin** → http://localhost/phpmyadmin

Click **"New"** → Database name: `agritrek` → Collation: `utf8mb4_unicode_ci` → **Create**

**Option A (Recommended) — Import the SQL file:**
1. Click on `agritrek` database
2. Click **Import** tab
3. Choose file: `agri-trek/docs/database_setup.sql`
4. Click **Go**

This creates all tables AND inserts demo data in one step. ✅

**Option B — Use artisan (after composer install):**
```bash
php artisan migrate
php artisan db:seed
```

---

### Step 6 — Fix storage permissions (Linux/Mac only)
```bash
chmod -R 775 storage bootstrap/cache
```
*(Windows users skip this step)*

---

### Step 7 — Create storage symlink
```bash
php artisan storage:link
```

---

### Step 8 — Run the application

**Option A — PHP built-in server (recommended for testing):**
```bash
php artisan serve
```
Open: **http://localhost:8000**

**Option B — Via Apache (XAMPP/WAMP):**
Open: **http://localhost/agri-trek/public**

For clean URLs on Apache, ensure `mod_rewrite` is enabled and the `.htaccess` file is in `public/`.

---

## 🔑 Login Credentials

| Role   | Email                   | Password |
|--------|-------------------------|----------|
| Admin  | admin@agritrek.com      | password |
| Farmer | farmer@agritrek.com     | password |

---

## 🐛 Troubleshooting Common Errors

### ❌ "Internal Server Error" or white screen
1. Open `.env` and set `APP_DEBUG=true`
2. Check `storage/logs/laravel.log` for the real error
3. Make sure `storage/` and `bootstrap/cache/` are writable

### ❌ "Class not found" errors
```bash
composer dump-autoload
```

### ❌ "No application encryption key" error
The `.env` file already has a key. If lost:
```bash
php artisan key:generate
```

### ❌ "SQLSTATE" or database errors
- Make sure MySQL is running in XAMPP/WAMP
- Verify `.env` has correct DB credentials
- Import `docs/database_setup.sql` via phpMyAdmin

### ❌ "View not found" or Blade errors
```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

### ❌ "Route not found" (404)
For Apache, make sure `mod_rewrite` is ON:
- XAMPP: Open `httpd.conf`, uncomment `LoadModule rewrite_module`
- Also ensure `AllowOverride All` in your virtual host config

### ❌ Blank page on XAMPP subdirectory
Set `APP_URL` in `.env`:
```env
APP_URL=http://localhost/agri-trek/public
```

---

## 📁 Important Files

| File | Purpose |
|------|---------|
| `.env` | App config with DB credentials & key |
| `docs/database_setup.sql` | Full DB schema + demo data |
| `storage/logs/laravel.log` | Error logs |
| `public/.htaccess` | Apache URL rewriting |

---

## 🔄 Quick Reset

To wipe and reseed the database:
```bash
php artisan migrate:fresh --seed
```
