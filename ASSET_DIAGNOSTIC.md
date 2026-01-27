# Asset Loading Diagnostic

## Quick Test:

1. **Test Direct Asset Access:**
   - Open: `http://localhost/test-assets.html`
   - Click the links to test if assets load directly
   - If assets load → The problem is with Laravel's `asset()` helper
   - If assets don't load → Apache configuration issue

2. **Check Browser Console:**
   - Open `http://localhost/business/dashboard`
   - Press **F12** → Go to **Network** tab
   - Reload page (F5)
   - Look for failed requests (red)
   - Check what URLs are being requested for assets

3. **Expected Asset URLs:**
   - Should be: `http://localhost/assets/css/bootstrap.min.css`
   - Should NOT be: `http://localhost/todpos/public/assets/css/bootstrap.min.css`

## Most Common Issues:

### Issue 1: Apache Not Restarted
**Solution:** Restart Apache in XAMPP Control Panel

### Issue 2: Browser Cache
**Solution:** 
- Press **Ctrl + Shift + Delete** → Clear cache
- Or use **Incognito/Private** window
- Or press **Ctrl + F5** for hard refresh

### Issue 3: Asset URLs Still Have Old Path
**Solution:** 
- Check browser DevTools → Network tab
- See what URLs are being requested
- If they show `/todpos/public/assets/...` → Laravel cache issue
- Run: `php artisan optimize:clear`

### Issue 4: Apache DocumentRoot Wrong
**Verify:**
- Open: `D:\XAMPP\apache\conf\httpd.conf`
- Line 252: Should be `DocumentRoot "D:/XAMPP/htdocs/todpos/public"`
- If wrong, fix it and restart Apache

## Quick Fix Commands:

```bash
# Clear all Laravel caches
php artisan optimize:clear

# Clear config
php artisan config:clear

# Clear views
php artisan view:clear
```

Then **restart Apache** and **clear browser cache**.
