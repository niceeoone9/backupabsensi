# INTEGRATION GUIDE
## Menghubungkan Web Attendance dengan Backend Mobile App

---

## 🔗 OVERVIEW

Sistem web attendance ini telah dilengkapi dengan **API Bridge** yang memungkinkan koneksi langsung ke backend server yang sama dengan aplikasi mobile Android.

---

## 🏗️ ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────┐
│                    USER (Web Browser)                        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│              Web Attendance Frontend                         │
│              (HTML/CSS/JavaScript)                           │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                  API Bridge (PHP)                            │
│         - api_bridge.php                                     │
│         - Formats data untuk mobile API                      │
│         - Handles authentication                             │
│         - Fallback ke demo mode jika API unreachable        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│         MOBILE BACKEND API SERVER                            │
│         https://bisma.bekasikab.go.id/api/                   │
│         - Same endpoints as mobile app                       │
│         - Same database                                      │
│         - Same business logic                                │
└─────────────────────────────────────────────────────────────┘
```

---

## 📡 API BRIDGE FEATURES

### **1. Automatic Connection**
```
✓ Tries to connect to mobile API first
✓ Fallback to demo mode if API unreachable
✓ Seamless user experience
✓ Error handling and retry logic
```

### **2. Data Format Compatibility**
```php
// Web attendance sends:
{
    "photo": "base64...",
    "latitude": "-6.2345678",
    "longitude": "107.1234567"
}

// API Bridge converts to mobile format:
{
    "FotoCheckIn": "base64...",
    "latCheckIn": "-6.2345678",
    "lngCheckIn": "107.1234567",
    "latAbsen": "-6.2345678",
    "lngAbsen": "107.1234567",
    "waktuCheckIn": "08:00:00",
    "tglAbsen": "2025-11-23",
    "KodeAbsenUser": "NIP"
}
```

### **3. Authentication Token Management**
```
✓ Stores token from mobile API login
✓ Includes Bearer token in all requests
✓ Handles token refresh
✓ Session management
```

---

## ⚙️ CONFIGURATION

### **Enable API Bridge**

Edit `frontend/assets/js/config.js`:

```javascript
const CONFIG = {
    // Set to true to connect with mobile backend
    USE_API_BRIDGE: true,
    
    // API Endpoints
    API: {
        LOGIN: '/api_bridge.php/api/auth/login',
        CHECKIN: '/api_bridge.php/api/attendance/checkin',
        CHECKOUT: '/api_bridge.php/api/attendance/checkout',
        // ...
    }
};
```

### **Mobile API Configuration**

Edit `frontend/api_bridge.php`:

```php
// Change this to your actual mobile API URL
define('MOBILE_API_BASE', 'https://bisma.bekasikab.go.id/api');

// For testing/development, you can point to staging:
// define('MOBILE_API_BASE', 'https://staging-api.bekasikab.go.id/api');
```

---

## 🔄 DATA FLOW

### **Login Flow:**

```
1. User enters NIP & Password in web app
   ↓
2. Web app calls: /api_bridge.php/api/auth/login
   ↓
3. API Bridge forwards to: https://bisma.bekasikab.go.id/api/login
   {
     "username": "NIP",
     "password": "password"
   }
   ↓
4. Mobile API responds with:
   {
     "success": true,
     "token": "eyJhbGc...",
     "data": {
       "nip": "...",
       "nama": "...",
       "jabatan": "..."
     }
   }
   ↓
5. API Bridge stores token & user data in session
   ↓
6. Web app receives response and shows dashboard
```

### **Check-in Flow:**

```
1. User clicks Check In
   ↓
2. Web app captures Location & Photo
   ↓
3. Web app calls: /api_bridge.php/api/attendance/checkin
   {
     "latitude": "-6.2345678",
     "longitude": "107.1234567",
     "photo": "data:image/jpeg;base64,..."
   }
   ↓
4. API Bridge transforms to mobile format:
   {
     "FotoCheckIn": "base64...",
     "latCheckIn": "-6.2345678",
     "lngCheckIn": "107.1234567",
     "latAbsen": "-6.2345678",
     "lngAbsen": "107.1234567",
     "waktuCheckIn": "08:00:00",
     "tglAbsen": "2025-11-23",
     "KodeAbsenUser": "NIP"
   }
   ↓
5. API Bridge sends to: https://bisma.bekasikab.go.id/api/absen
   with Bearer token
   ↓
6. Mobile API processes and saves to database
   ↓
7. Response flows back to web app
```

---

## 🔧 ENDPOINT MAPPING

### **Web App → Mobile API:**

| Web Endpoint | Mobile API Endpoint | Method | Description |
|-------------|---------------------|---------|-------------|
| `/api/auth/login` | `/login` | POST | User authentication |
| `/api/attendance/checkin` | `/absen` | POST | Check-in attendance |
| `/api/attendance/checkout` | `/absen` | POST | Check-out attendance |
| `/api/attendance/history` | `/daftar-absen` | GET | Attendance history |
| `/api/attendance/today` | `/absen` | GET | Today's attendance |

### **Field Name Mapping:**

| Web Field | Mobile Field | Description |
|-----------|--------------|-------------|
| `photo` | `FotoCheckIn` / `FotoCheckOut` | Base64 photo |
| `latitude` | `latCheckIn` / `latCheckOut` / `latAbsen` | GPS latitude |
| `longitude` | `lngCheckIn` / `lngCheckOut` / `lngAbsen` | GPS longitude |
| `timestamp` | `waktuCheckIn` / `waktuCheckOut` | Time |
| `date` | `tglAbsen` / `tglAbsensi` | Date |
| `user_id` | `KodeAbsenUser` | User NIP |

---

## 🧪 TESTING INTEGRATION

### **1. Test with Real Mobile API:**

```bash
# Check if mobile API is accessible
curl -I https://bisma.bekasikab.go.id/api/login

# Test login endpoint
curl -X POST https://bisma.bekasikab.go.id/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"YOUR_NIP","password":"YOUR_PASSWORD"}'

# Should return token if successful
```

### **2. Test API Bridge:**

```bash
# Test bridge connection
curl -X POST https://your-domain.com/api_bridge.php/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"nip":"YOUR_NIP","password":"YOUR_PASSWORD"}'
```

### **3. Monitor Logs:**

```bash
# Check API bridge logs
tail -f /content/web_attendance_system/logs/api_bridge.log

# You'll see connection attempts and responses
```

---

## 🔒 SECURITY CONSIDERATIONS

### **1. SSL/HTTPS Required**
```
✓ Always use HTTPS for API communication
✓ Never send credentials over HTTP
✓ Verify SSL certificates in production
```

### **2. Token Security**
```
✓ Store tokens securely (session/secure cookie)
✓ Include tokens in Authorization header
✓ Implement token refresh mechanism
✓ Clear tokens on logout
```

### **3. API Access Control**
```
✓ Whitelist web app IP on server
✓ Implement rate limiting
✓ Monitor for suspicious activity
✓ Log all API calls
```

---

## 🚀 DEPLOYMENT STEPS

### **Step 1: Deploy Web App**

```bash
# Upload files to web server
scp -r web_attendance_system user@server:/var/www/html/

# Set permissions
chmod 755 /var/www/html/web_attendance_system
```

### **Step 2: Configure API Bridge**

```bash
# Edit api_bridge.php
nano /var/www/html/web_attendance_system/frontend/api_bridge.php

# Update MOBILE_API_BASE to your actual server
define('MOBILE_API_BASE', 'https://bisma.bekasikab.go.id/api');
```

### **Step 3: Test Connection**

```bash
# Test from server
curl https://bisma.bekasikab.go.id/api/login

# Should be accessible
```

### **Step 4: Enable on Frontend**

```javascript
// Edit config.js
const CONFIG = {
    USE_API_BRIDGE: true,  // Enable bridge
    // ...
};
```

### **Step 5: Test Complete Flow**

1. Login via web app
2. Check console logs for API calls
3. Verify data appears in mobile app's database
4. Test check-in/check-out
5. Verify attendance records

---

## 📊 MONITORING

### **Check API Bridge Status:**

```php
// Add to api_bridge.php for health check
if (strpos($request_uri, '/health') !== false) {
    $mobile_api_status = @file_get_contents(MOBILE_API_BASE . '/health');
    
    echo json_encode([
        'web_app' => 'online',
        'mobile_api' => $mobile_api_status ? 'online' : 'offline',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}
```

### **View Logs:**

```bash
# API bridge logs
tail -f logs/api_bridge.log

# PHP error logs
tail -f /var/log/php-fpm/error.log

# Web server logs
tail -f /var/log/nginx/access.log
```

---

## 🔄 FALLBACK MODE

### **Automatic Fallback:**

```
If mobile API is unreachable:
1. API Bridge catches connection error
2. Switches to demo/local mode
3. Stores data locally (session)
4. Syncs when API comes back online
5. User experience uninterrupted
```

### **Manual Sync:**

```php
// Add sync function for offline data
function syncOfflineData() {
    // Get offline attendance records
    // Send to mobile API in batch
    // Mark as synced
}
```

---

## 🆘 TROUBLESHOOTING

### **Issue: API Bridge can't connect to mobile server**

**Solution:**
```bash
# Check DNS resolution
nslookup bisma.bekasikab.go.id

# Check network connectivity
ping bisma.bekasikab.go.id

# Check firewall rules
sudo iptables -L

# Test with curl
curl -v https://bisma.bekasikab.go.id/api/login
```

### **Issue: Authentication fails**

**Solution:**
```php
// Check field names match mobile API
// Verify token format
// Check session storage
// Review logs for errors
```

### **Issue: Data not appearing in mobile app**

**Solution:**
```bash
# Verify data reached API
tail -f logs/api_bridge.log

# Check mobile API logs
# Verify database connection
# Check data format matches
```

---

## ✅ VERIFICATION CHECKLIST

After integration:

```
[ ] Web app can login with real credentials
[ ] Token is received and stored
[ ] Check-in sends data to mobile API
[ ] Data appears in mobile app's database
[ ] Check-out works correctly
[ ] Attendance history shows web entries
[ ] Mobile app can see web attendance records
[ ] Logs show successful API calls
[ ] No errors in console
[ ] Fallback mode works if API down
```

---

## 🎯 RESULT

Setelah integration, sistem web attendance:

✅ **Menggunakan database yang sama** dengan mobile app  
✅ **Menggunakan backend API yang sama**  
✅ **Data attendance sinkron** antara web dan mobile  
✅ **Token authentication** dari server yang sama  
✅ **Fallback mode** jika server down  
✅ **Complete audit trail** di server  

---

## 📞 SUPPORT

**Configuration Help:**
- Review api_bridge.php code
- Check logs in logs/api_bridge.log
- Test endpoints manually with curl

**Integration Issues:**
- Verify mobile API is accessible
- Check field name mappings
- Review authentication flow
- Monitor server logs

---

**Status:** Ready for Integration  
**Last Updated:** 2025-11-23  
**Version:** 1.0.0
