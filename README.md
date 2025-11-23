# BKPSDM Backup Attendance System

Sistem backup absensi berbasis web untuk BKPSDMApp - Kabupaten Bekasi

## 🎯 Overview

Web-based attendance system yang berfungsi sebagai backup untuk aplikasi mobile BKPSDMApp. Sistem ini terhubung dengan backend yang sama dengan aplikasi Android, menggunakan database dan API yang sama untuk memastikan data tersingkronisasi.

## ✨ Features

- ✅ **Authentication System** - Login dengan NIP & password
- ✅ **Real-time Dashboard** - Status absensi hari ini
- ✅ **Check In/Out** - Absensi dengan GPS + foto
- ✅ **Attendance History** - Riwayat absensi
- ✅ **User Profile** - Informasi pengguna
- ✅ **Mobile Responsive** - Optimized untuk mobile & desktop
- ✅ **API Integration** - Terhubung dengan backend mobile app
- ✅ **Fallback Mode** - Demo mode jika API unreachable
- ✅ **WebView Ready** - Siap diintegrasikan ke aplikasi Android

## 🏗️ Architecture

```
┌─────────────────┐
│   Web Browser   │ User Interface
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   Frontend      │ HTML/CSS/JavaScript
│   (PWA Ready)   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   API Bridge    │ PHP Backend
│   (api_bridge)  │ Format conversion
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Mobile API     │ https://bisma.bekasikab.go.id/api
│  Backend Server │ Same as Android app
└─────────────────┘
```

## 📱 Screenshots

### Login Screen
Mobile-first design dengan branding BKPSDM

### Dashboard
Real-time clock, status absensi, quick actions

### Check-in Flow
3-step process: Location → Photo → Confirmation

### Attendance History
View past attendance records with filters

## 🚀 Quick Start

### Demo Mode

1. Visit: https://your-deployment-url.netlify.app
2. Login with demo credentials:
   - NIP: `demo`
   - Password: `demo123`
3. Test all features in demo mode

### Production Mode

1. Configure API endpoint in `frontend/api_bridge.php`
2. Login with real credentials
3. Data will sync with mobile backend

## 🔧 Configuration

### API Bridge Setup

Edit `frontend/api_bridge.php`:

```php
// Set your mobile API base URL
define('MOBILE_API_BASE', 'https://bisma.bekasikab.go.id/api');
```

### Enable API Integration

Edit `frontend/assets/js/config.js`:

```javascript
const CONFIG = {
    USE_API_BRIDGE: true,  // Enable mobile API connection
    // ...
};
```

## 📦 Project Structure

```
web_attendance_system/
├── frontend/
│   ├── index.html              # Main application
│   ├── demo.php                # Demo backend
│   ├── api_bridge.php          # API connector
│   └── assets/
│       ├── css/
│       │   ├── style.css       # Main styles
│       │   └── attendance.css  # Attendance specific
│       └── js/
│           ├── config.js       # Configuration
│           ├── app.js          # Main app logic
│           ├── auth.js         # Authentication
│           ├── attendance.js   # Attendance flow
│           ├── geolocation.js  # GPS handling
│           ├── webcam.js       # Camera handling
│           └── utils.js        # Utilities
├── backend/
│   └── config.php              # Backend configuration
├── docs/
│   ├── README.md               # This file
│   ├── INTEGRATION_GUIDE.md    # API integration guide
│   ├── SECURITY_POLICY.md      # Security guidelines
│   └── DEPLOYMENT_PACKAGE.md   # Deployment instructions
└── logs/
    └── api_bridge.log          # API call logs
```

## 🌐 Deployment

### Netlify (Recommended for Demo/Presentation)

1. Fork this repository
2. Connect to Netlify
3. Deploy automatically
4. Get public URL

### Shared Hosting (Production)

1. Upload files via FTP/SFTP
2. Configure PHP settings
3. Set up SSL certificate
4. Update API endpoints

### VPS (Advanced)

1. Clone repository
2. Install Nginx/Apache + PHP + MySQL
3. Configure virtual host
4. Set up SSL with Let's Encrypt
5. Configure firewall

See [DEPLOYMENT_PACKAGE.md](docs/DEPLOYMENT_PACKAGE.md) for detailed instructions.

## 🔒 Security

- ✅ HTTPS required for production
- ✅ Token-based authentication
- ✅ Session management
- ✅ Input validation & sanitization
- ✅ SQL injection protection
- ✅ XSS protection
- ✅ CSRF protection
- ✅ Rate limiting (configurable)
- ✅ Activity logging

See [SECURITY_POLICY.md](docs/SECURITY_POLICY.md) for complete security guidelines.

## 🔗 API Integration

Web attendance connects to the same backend as mobile app:

### Endpoints

| Web Endpoint | Mobile API | Description |
|-------------|-----------|-------------|
| `/api/auth/login` | `/login` | User authentication |
| `/api/attendance/checkin` | `/absen` | Check-in attendance |
| `/api/attendance/checkout` | `/absen` | Check-out attendance |
| `/api/attendance/history` | `/daftar-absen` | Attendance history |

### Data Format

Web app automatically converts data format to match mobile API:

```
Web format → API Bridge → Mobile API format
```

See [INTEGRATION_GUIDE.md](docs/INTEGRATION_GUIDE.md) for details.

## 🧪 Testing

### Manual Testing

1. **Login Flow**: Test with demo and real credentials
2. **Check-in**: GPS + camera + submission
3. **Check-out**: Complete flow
4. **History**: View past records
5. **Profile**: User information
6. **Responsive**: Test on mobile, tablet, desktop

### Browser Compatibility

- ✅ Chrome/Edge (90+)
- ✅ Firefox (88+)
- ✅ Safari (14+)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

### WebView Integration

Test in Android WebView:

```java
WebView webView = findViewById(R.id.webview);
webView.loadUrl("https://your-url.com");
```

## 📊 Monitoring

### View Logs

```bash
# API bridge logs
tail -f logs/api_bridge.log

# Application logs
tail -f logs/app.log
```

### Health Check

```bash
curl https://your-url.com/api/health
```

## 🆘 Troubleshooting

### Login Issues

- Verify credentials
- Check API connectivity
- Review logs for errors

### API Connection Failed

- Check mobile API is accessible
- Verify network connectivity
- Review firewall rules

### Camera/GPS Not Working

- Ensure HTTPS is enabled
- Check browser permissions
- Verify device compatibility

## 📞 Support

**Technical Documentation:**
- [Integration Guide](docs/INTEGRATION_GUIDE.md)
- [Security Policy](docs/SECURITY_POLICY.md)
- [Deployment Guide](docs/DEPLOYMENT_PACKAGE.md)

**Contact:**
- Email: support@bekasikab.go.id
- GitHub Issues: https://github.com/niceeoone9/backupabsensi/issues

## 📄 License

Copyright © 2025 BKPSDM Kabupaten Bekasi

This project is for internal use by BKPSDM Kabupaten Bekasi.

## 🎉 Acknowledgments

- BKPSDMApp Android Development Team
- BKPSDM Kabupaten Bekasi
- Dinas Komunikasi dan Informatika Kabupaten Bekasi

---

**Version:** 1.0.0  
**Last Updated:** 2025-11-23  
**Status:** Production Ready  
**Purpose:** Backup attendance system for BKPSDMApp

---

## 🚀 Quick Links

- **Live Demo:** https://your-deployment-url.netlify.app
- **Documentation:** [docs/](docs/)
- **Issues:** https://github.com/niceeoone9/backupabsensi/issues
- **Repository:** https://github.com/niceeoone9/backupabsensi
