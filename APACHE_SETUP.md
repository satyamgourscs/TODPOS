# Configure Apache to Point to Public Folder

## Steps to Get Shorter URLs (http://localhost/business/dashboard)

### 1. Edit XAMPP Apache Configuration

1. **Open XAMPP Control Panel**
2. **Click "Config" button next to Apache**
3. **Select "httpd.conf"**

4. **Find this line (around line 250-260):**
   ```apache
   DocumentRoot "C:/xampp/htdocs"
   <Directory "C:/xampp/htdocs">
   ```

5. **Change it to point to your public folder:**
   ```apache
   DocumentRoot "D:/XAMPP/htdocs/todpos/public"
   <Directory "D:/XAMPP/htdocs/todpos/public">
       Options Indexes FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

   **Note:** Make sure the path uses forward slashes `/` and matches your actual path!

6. **Also find and update this section (around line 280-290):**
   ```apache
   <Directory "C:/xampp/htdocs">
   ```
   
   Change to:
   ```apache
   <Directory "D:/XAMPP/htdocs/todpos/public">
       Options Indexes FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

### 2. Restart Apache

- Stop Apache in XAMPP Control Panel
- Start Apache again

### 3. Clear Laravel Caches

Run these commands in your project directory:
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 4. Access Your App

Now you can access your app at:
- ✅ `http://localhost/login` (instead of `http://localhost/todpos/public/login`)
- ✅ `http://localhost/business/dashboard` (instead of `http://localhost/todpos/public/business/dashboard`)
- ✅ All URLs will be shorter!

---

## Important Notes:

⚠️ **This will affect ALL projects in XAMPP!** 

If you have other projects in `htdocs`, they won't be accessible at `http://localhost` anymore. You'll need to access them via:
- `http://localhost/../other-project-name/public` (if they're Laravel)
- Or set up virtual hosts for each project

**Alternative:** If you want to keep other projects working, use **Option A (Virtual Host)** instead, which is cleaner and doesn't affect other projects.

---

## Troubleshooting:

If you get a 403 Forbidden error:
- Make sure the `Directory` section has `Require all granted`
- Check that the path is correct (use forward slashes `/`)
- Make sure Apache has read permissions to the folder

If other projects stop working:
- Consider using virtual hosts instead (see VIRTUAL_HOST_SETUP.md)
