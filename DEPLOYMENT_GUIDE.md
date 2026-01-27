# Deployment Guide for one.tryonedigital.com

This guide will help you deploy the TOD POS project to your live server at one.tryonedigital.com.

## Pre-Deployment Checklist

- [ ] PHP 7.4 or higher installed
- [ ] MySQL/MariaDB database created
- [ ] Composer installed on server
- [ ] cPanel access credentials
- [ ] FTP/SFTP access or cPanel File Manager access
- [ ] Domain one.tryonedigital.com configured

## Step 1: Prepare Files Locally

### 1.1 Optimize for Production

Run these commands in your local project directory:

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 1.2 Create Production Environment File

1. Copy `.env.production` to `.env` (or create new `.env` with production settings)
2. Update the following values:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://one.tryonedigital.com`
   - Database credentials (from cPanel)
   - Mail server settings

### 1.3 Generate Application Key (if needed)

```bash
php artisan key:generate
```

## Step 2: Upload Files to cPanel

### Option A: Using cPanel File Manager

1. Log into cPanel
2. Navigate to **File Manager**
3. Go to `public_html` folder
4. **Upload all project files** (you can zip them first, then extract on server)

### Option B: Using FTP/SFTP

1. Use FileZilla or similar FTP client
2. Connect to your server
3. Upload all files to `public_html` directory

### Important File Structure:

```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── Modules/
├── public/          (This contains index.php and assets)
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── resources/
├── routes/
├── storage/
├── vendor/          (Will be installed on server)
├── .env            (Create this on server)
├── .htaccess       (Root .htaccess)
├── artisan
└── composer.json
```

## Step 3: Configure Server Settings

### 3.1 Set Document Root

**Option 1: Point domain to public folder (Recommended)**

1. In cPanel, go to **Domains** → **Addon Domains** or **Subdomains**
2. Find `one.tryonedigital.com`
3. Set **Document Root** to: `public_html/public`

**Option 2: Move public folder contents**

If you can't change document root, move contents of `public/` to `public_html/` and update paths in `index.php`.

### 3.2 Update .htaccess Files

1. Copy `.htaccess.production` content to root `.htaccess` in `public_html/`
2. Ensure `public/.htaccess` exists with proper Laravel rewrite rules

### 3.3 Set PHP Version

1. In cPanel, go to **Select PHP Version**
2. Choose **PHP 7.4** or higher (8.0+ recommended)
3. Enable required extensions:
   - ✅ OpenSSL
   - ✅ PDO
   - ✅ Mbstring
   - ✅ Tokenizer
   - ✅ XML
   - ✅ Ctype
   - ✅ JSON
   - ✅ BCMath
   - ✅ Fileinfo
   - ✅ GD (for image processing)

## Step 4: Create Database

**Database Credentials (Already Created):**
- Database Host: `localhost`
- Database Name: `afpqeygf_one_TOD`
- Database User: `afpqeygf_one_TOD`
- Database Password: `todSg@123`
- Server IP: `37.27.71.198`

**Note:** The database and user are already created. You just need to verify they exist and have proper permissions.

## Step 5: Configure Environment File

1. In cPanel File Manager, navigate to `public_html/`
2. Create `.env` file (or edit existing)
3. Copy content from `.env.production` and update:

```env
APP_NAME='TOD POS'
APP_ENV=production
APP_KEY=base64:A0InMwoF//WjhSpqBXBownGuaN3NGM5E8WaLCYxd6yE=
APP_DEBUG=false
APP_URL=https://one.tryonedigital.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=afpqeygf_one_TOD
DB_USERNAME=afpqeygf_one_TOD
DB_PASSWORD=todSg@123

MAIL_DRIVER=smtp
MAIL_HOST=mail.tryonedigital.com
MAIL_PORT=465
MAIL_USERNAME=info@tryonedigital.com
MAIL_PASSWORD=todSg@123
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@tryonedigital.com
MAIL_FROM_NAME="TOD POS"
```

4. **Generate new application key** (via SSH):
   ```bash
   php artisan key:generate
   ```

## Step 6: Install Dependencies via SSH

1. In cPanel, go to **Terminal** (or use SSH)
2. Navigate to project directory:
   ```bash
   cd ~/public_html
   ```
3. Install Composer dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
   (If composer is not available, you may need to install it or use cPanel's Composer tool)

## Step 7: Set File Permissions

Via SSH/Terminal:

```bash
cd ~/public_html
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

Or via File Manager:
- Right-click `storage/` → **Change Permissions** → `775`
- Right-click `bootstrap/cache/` → **Change Permissions** → `775`

## Step 8: Import Database

### Option A: Via phpMyAdmin

1. In cPanel, go to **phpMyAdmin**
2. Select your database (`tryoned_pos`)
3. Click **Import**
4. Upload your database SQL file
5. Click **Go**

### Option B: Via SSH

```bash
mysql -u tryoned_user -p tryoned_pos < database_backup.sql
```

## Step 9: Run Migrations (if needed)

Via SSH:

```bash
cd ~/public_html
php artisan migrate --force
```

## Step 10: Create Storage Link

```bash
php artisan storage:link
```

## Step 11: Clear and Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 12: Test Your Deployment

1. Visit `https://one.tryonedigital.com`
2. Check if the site loads correctly
3. Test login functionality
4. Test key features (sales, purchases, etc.)
5. Check error logs: `storage/logs/laravel.log`

## Step 13: SSL Certificate (Important!)

1. In cPanel, go to **SSL/TLS Status**
2. Install SSL certificate for `one.tryonedigital.com`
3. Force HTTPS redirect (update `.htaccess`):

Add to `public/.htaccess`:

```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Security Checklist

- [ ] `APP_DEBUG=false` in `.env`
- [ ] Strong `APP_KEY` generated
- [ ] Secure database passwords
- [ ] `.env` file not publicly accessible
- [ ] SSL certificate installed
- [ ] File permissions set correctly (775 for storage, 644 for files)
- [ ] Remove unnecessary files (tests, .git, etc.)

## Troubleshooting

### 500 Internal Server Error

1. Check `.env` file configuration
2. Verify file permissions
3. Check error logs: `storage/logs/laravel.log`
4. Enable error display temporarily to see the issue

### Database Connection Error

1. Verify database credentials in `.env`
2. Check database user has proper permissions
3. Verify database host (usually `localhost`)

### Assets Not Loading

1. Run `php artisan storage:link`
2. Clear browser cache
3. Check file permissions on `public/assets/`

### Route Not Found

1. Run `php artisan route:cache`
2. Clear route cache: `php artisan route:clear`

### Permission Denied

1. Set `storage/` to 775
2. Set `bootstrap/cache/` to 775
3. Check owner of files (should be your cPanel user)

## Post-Deployment Maintenance

### Regular Updates

1. Keep Laravel and packages updated
2. Regularly backup database
3. Monitor error logs
4. Keep SSL certificate renewed

### Backup Strategy

1. **Database Backup**: Use cPanel's backup tool or phpMyAdmin
2. **File Backup**: Download entire `public_html` folder periodically
3. **Automated Backups**: Set up cron jobs for automatic backups

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log` for errors
2. Check cPanel error logs
3. Verify all configuration settings
4. Test with `APP_DEBUG=true` temporarily (remember to set back to `false`)

---

**Good luck with your deployment! 🚀**
