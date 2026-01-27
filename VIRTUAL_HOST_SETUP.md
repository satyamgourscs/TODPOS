# How to Set Up Shorter URLs for Your Laravel App

## Option 1: Virtual Host Setup (Recommended - Cleanest URLs)

This will let you access your app at `http://todpos.local` instead of `http://localhost/todpos/public`

### Steps:

1. **Edit Windows Hosts File:**
   - Open Notepad as Administrator
   - Open file: `C:\Windows\System32\drivers\etc\hosts`
   - Add this line at the bottom:
     ```
     127.0.0.1    todpos.local
     ```
   - Save and close

2. **Edit XAMPP Apache Virtual Hosts:**
   - Open: `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
   - Add this at the bottom:
     ```apache
     <VirtualHost *:80>
         ServerName todpos.local
         DocumentRoot "D:/XAMPP/htdocs/todpos/public"
         <Directory "D:/XAMPP/htdocs/todpos/public">
             Options Indexes FollowSymLinks
             AllowOverride All
             Require all granted
         </Directory>
     </VirtualHost>
     ```

3. **Update .env file:**
   - Change `APP_URL=http://localhost/todpos/public`
   - To: `APP_URL=http://todpos.local`

4. **Restart Apache in XAMPP Control Panel**

5. **Access your app at:** `http://todpos.local`

---

## Option 2: Keep Current Setup but Use Shorter Path

If you prefer to keep using `localhost`, you can access it at:
- `http://localhost/todpos/public` (current - long)
- Or configure XAMPP to point `http://localhost` directly to the `public` folder

---

## After Setup:

1. Clear Laravel caches:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

2. Restart Apache

3. Access your app with the new shorter URL!
