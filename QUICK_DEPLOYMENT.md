# Quick Deployment Checklist for one.tryonedigital.com

## Server Information
- **Domain:** one.tryonedigital.com
- **Server IP:** 37.27.71.198
- **Database Name:** afpqeygf_one_TOD
- **Database User:** afpqeygf_one_TOD
- **Database Password:** todSg@123
- **Database Host:** localhost

## Quick Steps

### 1. Upload Files
- Upload all project files to `public_html/` via FTP or cPanel File Manager

### 2. Create .env File
Create `.env` file in `public_html/` with this content:

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

BROADCAST_DRIVER=log
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_DRIVER=sync

MAIL_DRIVER=smtp
MAIL_HOST=mail.tryonedigital.com
MAIL_PORT=465
MAIL_USERNAME=info@tryonedigital.com
MAIL_PASSWORD=todSg@123
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@tryonedigital.com
MAIL_FROM_NAME="TOD POS"

LOG_CHANNEL=stack
LOG_LEVEL=error
TIMEZONE=Asia/Kolkata
FILESYSTEM_DISK=public
```

### 3. Set Document Root
- In cPanel → Domains → Set Document Root to: `public_html/public`

### 4. Set PHP Version
- cPanel → Select PHP Version → Choose PHP 7.4 or higher
- Enable: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo, GD

### 5. Install Dependencies (via SSH)
```bash
cd ~/public_html
composer install --no-dev --optimize-autoloader
```

### 6. Set Permissions
```bash
chmod -R 775 storage bootstrap/cache
```

### 7. Generate Key & Optimize
```bash
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### 8. Import Database
- Via phpMyAdmin: Import your database SQL file to `afpqeygf_one_TOD` database

### 9. Test
- Visit: https://one.tryonedigital.com
- Test login and key features

### 10. SSL Certificate
- Install SSL certificate in cPanel
- Add HTTPS redirect in `public/.htaccess`

## Important Notes
- Keep `APP_DEBUG=false` in production
- Database credentials are already set above
- Make sure `.env` file is not publicly accessible
- Set proper file permissions (775 for storage, 644 for files)
