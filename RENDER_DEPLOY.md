# Deploying to Render - Checklist

## Pre-Deployment Checklist ✓

- [x] **BASE_URL** - Now auto-detects server URL (no hardcoded localhost)
- [x] **SMTP_FROM_EMAIL** - Changed to use Gmail (same as username for reliability)
- [x] **render.yaml** - Updated with proper start command and permissions
- [x] **PHPMailer** - Included in vendor/ folder via Composer

## Render Deployment Steps

### 1. Create Git Repository (if not already)
```bash
cd akogwapo
git init
git add .
git commit -m "IRECSTEM 2026 - Ready for deployment"
```

### 2. Push to GitHub
```bash
git remote add origin https://github.com/YOUR_USERNAME/irecstem2026.git
git push -u origin main
```

### 3. Deploy to Render
1. Go to https://render.com
2. Click **"New +"** → **"Static Site"** (for free) or **"Web Service"**
3. Connect your GitHub repo
4. Set these settings:

| Setting | Value |
|---------|-------|
| **Root Directory** | `public` |
| **Build Command** | (leave empty or `composer install`) |
| **Publish Directory** | `.` |
| **PHP Version** | `8.2` |

5. Add Environment Variables:
   - `PHP_VERSION` = `8.2`

6. Click **"Create Static Site"**

### 4. After Deployment - Setup Admin Account
1. Visit: `https://your-site.onrender.com/setup-admin.php`
2. Create your admin account
3. **IMPORTANT:** Delete `setup-admin.php` after setup

### 5. Verify Functions
- [ ] Registration works (users get email codes)
- [ ] Login/logout works
- [ ] Admin panel accessible
- [ ] Paper submission works
- [ ] Email notifications send

## Known Limitations on Free Tier

| Feature | Status |
|---------|--------|
| Registration | ✅ Works |
| Email sending | ✅ Works (Gmail SMTP) |
| Paper upload | ✅ Works |
| Admin panel | ✅ Works |
| User dashboard | ✅ Works |
| Data persistence | ⚠️ Note 1 |
| Cron jobs | ❌ Not available on free tier |

### Note 1: Data Persistence on Free Tier
Render's free tier uses ephemeral filesystem. **Data will be lost on redeploy/sleep.**

**Solutions:**
1. **Upgrade to Render Paid Plan** (~$7/month) - persistent disk
2. **Use external database** - Replace JSON files with MySQL/PostgreSQL
3. **Git-tracked data** - Commit data files to git (not recommended for production)

## Troubleshooting

### "Permission denied" errors
Add to Build Command:
```bash
chmod -R 755 data/ uploads/
```

### Emails not sending
1. Verify Gmail App Password is correct
2. Enable 2FA on Gmail account
3. Check Gmail "Less secure app access" settings

### Session issues
Render may need session configuration. Add to `config.php`:
```php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
```
