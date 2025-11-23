# DEPLOYMENT GUIDE - NETLIFY

## 🚀 Deploy ke Netlify

### Method 1: Via Netlify Dashboard (Recommended)

1. **Login ke Netlify**
   - Visit: https://app.netlify.com
   - Sign up/Login dengan GitHub account

2. **Connect Repository**
   - Click "Add new site" → "Import an existing project"
   - Choose "Deploy with GitHub"
   - Authorize Netlify to access GitHub
   - Select repository: `niceeoone9/backupabsensi`

3. **Configure Build Settings**
   ```
   Build command: (leave empty or "echo 'No build'")
   Publish directory: frontend
   ```

4. **Deploy**
   - Click "Deploy site"
   - Wait 1-2 minutes
   - Get your URL: https://[random-name].netlify.app

5. **Custom Domain (Optional)**
   - Go to "Domain settings"
   - Add custom domain: backup-absensi.netlify.app
   - SSL certificate auto-generated

### Method 2: Via Netlify CLI

```bash
# Install Netlify CLI
npm install -g netlify-cli

# Login
netlify login

# Deploy
cd /content/web_attendance_system
netlify deploy --prod --dir=frontend

# Follow prompts and get URL
```

### Method 3: Drag & Drop

1. Visit: https://app.netlify.com/drop
2. Drag folder `frontend/` to browser
3. Instant deployment!

---

## ⚙️ NETLIFY CONFIGURATION

### Environment Variables

Set in Netlify Dashboard → Site settings → Environment variables:

```
MOBILE_API_BASE = https://bisma.bekasikab.go.id/api
DEMO_MODE = true
```

### PHP Support (For Full API Bridge)

**Note:** Netlify doesn't support PHP natively. For demo:

**Option A: Use Demo Mode**
- Frontend calls demo.php endpoints
- Perfect for presentation

**Option B: Netlify Functions**
- Convert api_bridge.php to serverless function
- See `netlify/functions/api-proxy.js` example

---

## 🔗 AFTER DEPLOYMENT

### 1. Get Your URL

After deployment, you'll get URL like:
```
https://bkpsdm-backup-attendance.netlify.app
```

### 2. Test the Site

- Open URL in browser
- Login with demo credentials:
  - NIP: `demo`
  - Password: `demo123`
- Test all features

### 3. Share with Atasan

```
Subject: Web Attendance Backup System - Demo

Bapak/Ibu Yth,

Berikut adalah demo sistem backup absensi berbasis web 
untuk BKPSDMApp:

URL: https://[your-url].netlify.app

Login Demo:
- NIP: demo
- Password: demo123

Fitur:
✓ Login system
✓ Check-in/out dengan GPS + foto
✓ Dashboard real-time
✓ Riwayat absensi
✓ Mobile responsive
✓ WebView ready

Sistem ini terhubung dengan backend yang sama dengan 
aplikasi Android untuk memastikan data tersingkronisasi.

Mohon review dan feedback.

Terima kasih.
```

---

## 📊 MONITORING

### Netlify Analytics

- Visit: Netlify Dashboard → Analytics
- Monitor:
  - Page views
  - Unique visitors
  - Top pages
  - Traffic sources

### Logs

- Visit: Netlify Dashboard → Deploys → [Latest deploy] → Deploy log
- Check for errors

---

## 🔄 UPDATE DEPLOYMENT

### Auto Deploy (Recommended)

Once connected to GitHub:
```bash
# Any push to main branch auto-deploys
git add .
git commit -m "Update features"
git push origin main

# Netlify auto-detects and deploys
```

### Manual Deploy

```bash
netlify deploy --prod --dir=frontend
```

---

## 🆘 TROUBLESHOOTING

### Issue: Build Failed

**Solution:**
- Check build logs in Netlify dashboard
- Verify netlify.toml configuration
- Ensure all files committed to Git

### Issue: 404 on API Calls

**Solution:**
- Set DEMO_MODE=true in config
- Or setup Netlify Functions for API proxy

### Issue: Camera/GPS Not Working

**Solution:**
- Ensure site is HTTPS (Netlify provides this)
- Check browser permissions

---

## 💡 TIPS

1. **Use Demo Mode for Presentation**
   - Set DEMO_MODE=true
   - No backend required
   - Perfect for showing UI/UX

2. **Custom Domain**
   - Makes URL professional
   - Example: backup-absensi.netlify.app

3. **Branch Previews**
   - Test changes before production
   - Create branch, push, get preview URL

4. **Password Protection**
   - Netlify allows site password
   - Useful before public launch

---

## ✅ POST-DEPLOYMENT CHECKLIST

```
[ ] Site deployed successfully
[ ] HTTPS enabled (auto by Netlify)
[ ] Demo login working
[ ] All pages accessible
[ ] Mobile responsive verified
[ ] Camera permission works
[ ] GPS permission works
[ ] Shared URL with team
[ ] Feedback collected
[ ] Ready for next phase (production hosting)
```

---

## 🎯 NEXT STEPS

After successful demo on Netlify:

1. **Collect Feedback** from atasan
2. **Make Adjustments** based on feedback
3. **Plan Production Deployment** to proper hosting with PHP support
4. **Setup Real API Integration**
5. **UAT Testing** with real users
6. **Production Launch**

---

**Deployment Status:** Ready to deploy  
**Expected Time:** 5-10 minutes  
**Cost:** FREE (Netlify free tier)  
**URL:** Will be provided after deployment
