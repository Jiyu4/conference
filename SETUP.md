# IRECSTEM 2026 - Registration System Setup

## Quick Setup

### 1. Configure SMTP Email Settings

Edit `public/config.php` and update these lines with your Gmail credentials:

```php
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'your-app-password');    // Gmail App Password
```

**To get a Gmail App Password:**
1. Enable 2-Factor Authentication on your Google Account
2. Go to https://myaccount.google.com/security
3. Click "App passwords" (under 2-Step Verification)
4. Create a new app password for "Mail"
5. Use that 16-character password in SMTP_PASSWORD

### 2. Create Admin Account

1. Open your browser and go to: `http://localhost/akogwapo/setup-admin.php`
2. Fill in your name, email, and password
3. Click "Create Admin Account"
4. **IMPORTANT:** Delete `setup-admin.php` after creating the admin account

### 3. Test the System

1. Go to `http://localhost/akogwapo/auth.php?register=1`
2. Register with a test email
3. Check your email for the verification code
4. Enter the code to complete registration
5. Login to see the participant dashboard

### 4. Access Admin Panel

1. Go to `http://localhost/akogwapo/auth.php`
2. Login with your admin email and password
3. Access the admin panel to:
   - View all registrations
   - Edit participant info (name, email)
   - Delete registrations
   - Add participants manually
   - Export registrations to CSV
   - Create additional admin accounts

## How Registration Works

### For Participants:
1. Enter **Name** and **Email** only
2. Receive a 6-digit verification code via email
3. Enter code to complete registration
4. Access their dashboard to update name only

### For Admins:
1. Login with email and password
2. Access admin panel to manage all registrations
3. Can edit any participant's name and email
4. Can manually add participants (no email verification needed)
5. Can export all data to CSV

## File Structure

```
public/
├── auth.php          # Login/Register pages
├── verify_login.php   # Login code verification
├── dashboard.php     # Participant dashboard
├── admin/
│   └── index.php     # Admin panel
├── config.php        # Configuration (DB, SMTP)
├── setup-admin.php   # First-time admin setup (delete after use)
├── data/             # JSON database files
│   ├── users.json
│   └── papers.json
└── vendor/           # PHPMailer (via Composer)
```

## Database

Data is stored as JSON files in `public/data/`:
- `users.json` - All registered users and admins
- `papers.json` - Paper submissions (if re-enabled)

## Troubleshooting

### Email not sending?
- Make sure SMTP credentials are correct
- Check if Gmail App Password is correct
- For testing, codes are also shown in the browser message

### "Session expired" errors?
- Check that PHP sessions are working
- Make sure cookies are enabled in browser
