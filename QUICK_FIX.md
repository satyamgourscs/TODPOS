# QUICK FIX: Make http://localhost/business/dashboard Work

## The Problem
Apache's DocumentRoot is still pointing to `htdocs`, not to your `public` folder. That's why `http://localhost/business/dashboard` doesn't work.

## The Solution (2 Minutes)

### Step 1: Edit Apache Config
1. Open **XAMPP Control Panel**
2. Click **"Config"** button next to Apache
3. Click **"httpd.conf"**

### Step 2: Find and Change DocumentRoot
Press **Ctrl+F** and search for: `DocumentRoot`

You'll find something like:
```apache
DocumentRoot "C:/xampp/htdocs"
```

**Change it to:**
```apache
DocumentRoot "D:/XAMPP/htdocs/todpos/public"
```

### Step 3: Find and Change Directory
Search for: `<Directory "C:/xampp/htdocs">`

**Change it to:**
```apache
<Directory "D:/XAMPP/htdocs/todpos/public">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### Step 4: Save and Restart
1. **Save** the httpd.conf file (Ctrl+S)
2. **Stop** Apache in XAMPP Control Panel
3. **Start** Apache again

### Step 5: Update .env
Change this line in `.env`:
```
APP_URL=http://localhost/todpos/public
```
To:
```
APP_URL=http://localhost
```

### Step 6: Clear Cache
Run in terminal:
```bash
php artisan config:clear
php artisan route:clear
```

### Step 7: Test
Now try: `http://localhost/business/dashboard` ✅

---

## ⚠️ Important
This will make `http://localhost` point ONLY to your todpos project. Other projects won't be accessible at `http://localhost` anymore.

If you need other projects to work, use Virtual Hosts instead (see VIRTUAL_HOST_SETUP.md).
