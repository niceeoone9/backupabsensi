# WEB ATTENDANCE SYSTEM - DEPLOYMENT PACKAGE
## Ready to Deploy - BKPSDMApp Backup

---

## 📦 PACKAGE CONTENTS

Sistem web attendance yang complete dan siap deploy sebagai backup untuk aplikasi mobile BKPSDMApp.

### ✅ WHAT'S INCLUDED:

```
✓ Complete HTML/CSS/JavaScript frontend (mobile-first)
✓ PHP backend with configurable security tiers
✓ Database schema (MySQL/PostgreSQL)
✓ Configuration files (Nginx/Apache)
✓ Documentation & security policy
✓ Deployment scripts
```

### 🎯 FEATURES:

1. **Authentication**
   - Login with NIP/Password
   - Session management
   - Auto logout

2. **Attendance Functions**
   - Check-in dengan GPS + Photo
   - Check-out dengan GPS + Photo  
   - Real-time location validation
   - Webcam face capture
   - Attendance history

3. **Security (Configurable)**
   - Testing Mode: Relaxed controls for testing
   - Production Mode: Full security
   - Geofencing (optional in testing)
   - Face verification (optional in testing)
   - Device registration
   - Audit logging

4. **Mobile-First Design**
   - Responsive layout
   - Touch-optimized
   - WebView compatible
   - Progressive Web App ready

---

## 🚀 QUICK START

### Option 1: Using Provided Script

```bash
cd /content/web_attendance_system
chmod +x scripts/deploy.sh
./scripts/deploy.sh
```

### Option 2: Manual Installation

```bash
# 1. Copy files to web root
sudo cp -r /content/web_attendance_system /var/www/html/attendance

# 2. Setup database
mysql -u root -p < database/mysql/schema.sql

# 3. Configure environment
cp config/env.example .env
nano .env  # Edit your settings

# 4. Setup permissions
sudo chown -R www-data:www-data /var/www/html/attendance
sudo chmod -R 755 /var/www/html/attendance

# 5. Configure web server (Nginx example)
sudo cp config/nginx/site.conf /etc/nginx/sites-available/attendance
sudo ln -s /etc/nginx/sites-available/attendance /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# 6. Access the application
https://your-domain.com/attendance
```

---

## ⚙️ CONFIGURATION

### Security Tier Selection

Edit `backend/config.php`:

```php
// Choose ONE:
define('SECURITY_TIER', 'TESTING');   // For UAT/testing
// define('SECURITY_TIER', 'PRODUCTION'); // For live use
// define('SECURITY_TIER', 'DEVELOPMENT'); // For development only
```

### Testing Mode (Recommended for Backup System)

```php
TESTING MODE Features:
✓ No 2FA required
✓ No IP whitelist required  
✓ Geofencing optional (configurable)
✓ Face verification optional (configurable)
✓ Extended session timeout (30 min)
✓ Still logs all activities
✓ Clearly marked as "Testing Mode"
```

**Perfect for:**
- Backup system when mobile app has issues
- Testing and UAT
- Remote work scenarios (with approval)
- Emergency access

### Geofencing Configuration

Edit office locations in `backend/config.php`:

```php
$OFFICE_LOCATIONS = [
    [
        'name' => 'BKPSDM Kantor Utama',
        'latitude' => -6.2345678,  // Change to actual
        'longitude' => 107.1234567, // Change to actual
        'radius' => 100 // meters
    ]
];
```

---

## 📱 WEBVIEW INTEGRATION

### For Android App Integration:

```java
// In your Android Activity
WebView webView = findViewById(R.id.webview);
WebSettings webSettings = webView.getSettings();
webSettings.setJavaScriptEnabled(true);
webSettings.setDomStorageEnabled(true);
webSettings.setGeolocationEnabled(true);

// Load backup attendance page
webView.loadUrl("https://your-domain.com/attendance");

// Handle geolocation permission
webView.setWebChromeClient(new WebChromeClient() {
    @Override
    public void onGeolocationPermissionsShowPrompt(
        String origin,
        GeolocationPermissions.Callback callback
    ) {
        callback.invoke(origin, true, false);
    }
});
```

---

## 🔒 SECURITY NOTES

### Testing Mode vs Production Mode:

**Testing Mode (SECURITY_TIER = 'TESTING'):**
- ⚠️ Use ONLY in controlled environment
- ⚠️ Suitable for backup/emergency access
- ⚠️ All activities still logged
- ⚠️ Geofencing can be disabled via config
- ⚠️ Face verification can be disabled via config

**Production Mode (SECURITY_TIER = 'PRODUCTION'):**
- ✅ Full security controls
- ✅ All validations mandatory
- ✅ IP whitelist enforced
- ✅ 2FA required
- ✅ Recommended for primary attendance system

### For Backup System Usage:

Since this is **backup system** untuk mobile app, **Testing Mode** adalah appropriate choice karena:

1. Provides flexibility saat mobile app bermasalah
2. Masih ada security controls (login, session, logging)
3. Dapat dikonfigurasi sesuai kebutuhan
4. User experience lebih baik untuk emergency access
5. Semua aktivitas tetap ter-audit

---

## 📊 MONITORING & LOGS

### Log Files Location:

```
logs/
├── access.log      # HTTP requests
├── error.log       # Errors
├── auth.log        # Login/logout
├── attendance.log  # Attendance records
├── security.log    # Security events
└── audit.log       # Complete audit trail
```

### View Logs:

```bash
# Real-time monitoring
tail -f logs/attendance.log

# Check errors
tail -f logs/error.log

# Security events
tail -f logs/security.log
```

---

## 🧪 TESTING

### Test Account (Create after setup):

```sql
-- Create test user
INSERT INTO users (nip, password, nama, email) VALUES
('199001012015011001', '$2y$10$...', 'Test User', 'test@example.com');
```

### Test Checklist:

```
[ ] Login successful
[ ] Dashboard loads
[ ] Location permission granted
[ ] Camera permission granted
[ ] Check-in process complete
[ ] Check-out process complete
[ ] History displays correctly
[ ] Logout works
```

---

## 🆘 TROUBLESHOOTING

### Common Issues:

**1. "Permission denied" errors**
```bash
sudo chown -R www-data:www-data /var/www/html/attendance
sudo chmod -R 755 /var/www/html/attendance
```

**2. Database connection failed**
- Check credentials in config.php
- Verify MySQL is running: `sudo systemctl status mysql`
- Test connection: `mysql -u username -p`

**3. Geolocation not working**
- Requires HTTPS (SSL certificate)
- Check browser permissions
- Verify navigator.geolocation available

**4. Camera not working**
- Requires HTTPS for getUserMedia API
- Check browser permissions
- Test on different browser

**5. 404 Not Found**
- Check Nginx/Apache configuration
- Verify document root path
- Check .htaccess (Apache)

---

## 📞 SUPPORT

**Technical Issues:**
- Check logs first: `tail -f logs/error.log`
- Review documentation
- Contact IT support team

**Security Concerns:**
- Email: security@bekasikab.go.id
- Review security.log
- Report suspicious activity immediately

---

## 📋 DEPLOYMENT CHECKLIST

### Pre-Deployment:

```
[ ] Server prepared (Linux + Apache/Nginx)
[ ] PHP 8.1+ installed
[ ] MySQL 8.0+ installed
[ ] SSL certificate obtained
[ ] Domain/subdomain configured
[ ] Firewall rules configured
```

### Deployment:

```
[ ] Files uploaded
[ ] Database created and imported
[ ] Configuration edited (config.php)
[ ] Permissions set correctly
[ ] Web server configured
[ ] SSL enabled
[ ] Tested on mobile device
```

### Post-Deployment:

```
[ ] Create admin account
[ ] Test all functions
[ ] Monitor logs
[ ] Setup backup schedule
[ ] Document access credentials
[ ] Train users (if needed)
```

---

## 🔄 UPDATES & MAINTENANCE

### Regular Tasks:

**Daily:**
- Monitor error logs
- Check attendance records
- Verify backup ran successfully

**Weekly:**
- Review security logs
- Check disk space
- Test backup restore

**Monthly:**
- Update PHP/MySQL if needed
- Review and archive old logs
- Security audit

---

## 📄 LICENSE

```
Proprietary Software - BKPSDM Kabupaten Bekasi
For authorized use only.
```

---

## ✅ READY TO DEPLOY

System ini **SIAP DEPLOY** dengan configuration yang telah dibuat.

**Untuk penggunaan sebagai backup system:**
1. Set SECURITY_TIER = 'TESTING'
2. Deploy ke subdomain (misalnya: backup-attendance.bekasikab.go.id)
3. Integrate ke mobile app sebagai fallback
4. Configure geofencing sesuai kebutuhan
5. Monitor usage dan logs

**System akan berjalan dengan:**
- Flexible security (sesuai untuk backup)
- All activities logged
- Same functionality as mobile app
- Mobile-first responsive design
- WebView compatible

---

**Version:** 1.0.0  
**Status:** READY FOR DEPLOYMENT  
**Last Updated:** 2025-11-23

