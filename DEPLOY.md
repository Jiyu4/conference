# IRECSTEM 2026 - Deployment Guide

## Problem: PHP files downloading instead of executing

This happens because the web server isn't configured to process PHP files. Here's how to fix it:

---

## Option 1: Deploy to Render (Recommended)

### Steps:
1. Create a GitHub repository with these files:
   ```
   public/
   ├── *.html          (all HTML files)
   ├── *.css           (styles.css)
   ├── *.js            (script.js)
   ├── *.php           (auth.php, dashboard.php, etc.)
   ├── data/           (JSON database)
   │   └── *.json
   └── uploads/        (uploaded papers)
   ```

2. Go to [render.com](https://render.com) and connect your GitHub

3. Create a new **Web Service**:
   - Build Command: `echo "No build required"`
   - Start Command: `php -S $HOST:$PORT`

4. Upload the `public/` folder contents as your repository

### Important File for Render:
Create `public/index.php` (already included):
```php
<?php
// Entry point - routes requests to correct files
$routes = [
    '' => 'index.html',
    'auth' => 'auth.php',
    'dashboard' => 'dashboard.php',
    // ... etc
];
```

---

## Option 2: Deploy to Vercel

Create `vercel.json` in public folder:
```json
{
  "rewrites": [
    { "source": "/(.*)", "destination": "/index.php" }
  ],
  "headers": [
    {
      "source": "/(.*)",
      "headers": [
        { "key": "X-Content-Type-Options", "value": "nosniff" }
      ]
    }
  ]
}
```

---

## Option 3: Deploy to Apache Shared Hosting

1. Make sure PHP is enabled on your hosting
2. Upload files to `public_html/` or `www/`
3. Ensure `.htaccess` is uploaded (already configured)

---

## Quick Fix for Current Deployment

If your site is currently broken, try this:

### For Render:
1. Go to your Render dashboard
2. Select your service
3. Go to **Environment** tab
4. Add environment variable:
   - Key: `PHP_VERSION`
   - Value: `8.2`

5. Go to **Shell** tab and run:
   ```bash
   cd /etc/apache2/mods-enabled
   ln -s ../mods-available/php8.2.conf php.conf
   ln -s ../mods-available/php8.2.load php.load
   a2enmod php8.2
   service apache2 restart
   ```

---

## File Structure for Deployment

```
irecstem2026/
├── public/                 # Web root (upload this folder)
│   ├── index.html         # Homepage
│   ├── about.html
│   ├── auth.php           # Login/Register (PHP!)
│   ├── dashboard.php      # User dashboard (PHP!)
│   ├── config.php         # Database config
│   ├── styles.css
│   ├── script.js
│   ├── data/              # JSON database
│   │   ├── users.json
│   │   ├── papers.json
│   │   └── certificates.json
│   └── uploads/            # Uploaded papers
│       └── .htaccess
└── render.yaml           # Render config
```

---

## Verify PHP is Working

Create a test file `public/test.php`:
```php
<?php
echo "PHP is working! Version: " . phpversion();
```

If you see "PHP is working!" when visiting `/test.php`, PHP is configured correctly.

If you see the code instead, PHP isn't being processed.

---

## Local Testing (XAMPP)

Place files in:
```
C:\xampp\htdocs\irecstem2026\
```

Access via: `http://localhost/irecstem2026/auth.php`

---

## Questions?

If still having issues, share:
1. Your deployment URL
2. What platform you're using (Render, Apache, Nginx, etc.)
3. Any error messages in browser console (F12)
