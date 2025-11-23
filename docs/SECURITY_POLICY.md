# SECURITY POLICY
## Web Attendance System - BKPSDM

---

## ⚠️ CRITICAL REQUIREMENTS

### **This system MUST ONLY be deployed with:**

1. ✅ **Written authorization** from Kepala BKPSDM
2. ✅ **Security audit** completed and approved
3. ✅ **IT Security team** sign-off
4. ✅ **Legal department** clearance
5. ✅ **Proper infrastructure** (firewall, SSL, monitoring)

### **UNAUTHORIZED DEPLOYMENT = SECURITY VIOLATION**

---

## 🔒 SECURITY TIERS EXPLAINED

### **TIER 1: PRODUCTION** ⭐⭐⭐⭐⭐
**Use When:** Live operations, real employees, actual data

**Security Controls:**
```
Authentication:
- ✅ Username + Password (minimum 12 characters)
- ✅ 2FA via SMS/Email OTP mandatory
- ✅ Password complexity requirements
- ✅ Account lockout after 3 failed attempts
- ✅ Password expiry (90 days)

Authorization:
- ✅ Role-based access control (RBAC)
- ✅ Session-based with secure cookies
- ✅ Token expiration (5 minutes)
- ✅ Automatic logout on inactivity

Network Security:
- ✅ IP Whitelist (office network ONLY)
- ✅ HTTPS mandatory (TLS 1.3)
- ✅ Certificate pinning recommended
- ✅ VPN/Proxy detection & blocking
- ✅ DDoS protection

Location Validation:
- ✅ Geofencing (100m radius from office)
- ✅ IP geolocation cross-check
- ✅ GPS coordinates validation
- ✅ Impossible travel detection

Biometric Validation:
- ✅ Webcam face capture mandatory
- ✅ Liveness detection (blink, smile, turn head)
- ✅ Face matching dengan database (>85% similarity)
- ✅ Multiple attempts if failed (max 3)
- ✅ Anti-spoofing checks

Device Security:
- ✅ Device fingerprinting
- ✅ Device registration required
- ✅ Admin approval for new devices
- ✅ Max 2 devices per user
- ✅ Device revocation capability

Rate Limiting:
- ✅ Max 2 check-ins per day (in/out)
- ✅ Max 5 login attempts per hour
- ✅ Max 10 API calls per minute
- ✅ IP-based throttling

Audit & Logging:
- ✅ Comprehensive logging (who, what, when, where)
- ✅ Tamper-proof logs (write-only)
- ✅ Real-time alerting on anomalies
- ✅ Log retention (1 year minimum)
- ✅ SIEM integration

Data Protection:
- ✅ Database encryption at rest
- ✅ Encrypted file storage
- ✅ Secure key management
- ✅ Data sanitization on input
- ✅ SQL injection prevention
- ✅ XSS prevention
- ✅ CSRF protection
```

**Compliance:**
- ✅ UU PDP No. 27/2022
- ✅ BSSN Guidelines
- ✅ ISO 27001 controls
- ✅ GDPR (if applicable)

---

### **TIER 2: TESTING** ⭐⭐⭐
**Use When:** UAT, integration testing, security testing

**⚠️ REQUIREMENTS:**
```
ONLY in controlled environment:
- ✅ Separate test server (NOT production)
- ✅ Test database (isolated from prod)
- ✅ Limited access (authorized testers only)
- ✅ Clearly marked as "TESTING MODE"
- ✅ Auto-watermark on all pages
- ✅ Different domain (test.bekasikab.go.id)
```

**Security Controls (Configurable):**
```
Authentication:
- ✅ Username + Password (8 characters minimum)
- ⚠️ 2FA optional (can be disabled for testing)
- ✅ Session-based auth
- ⚠️ Extended session (30 minutes)

Authorization:
- ✅ Basic RBAC
- ⚠️ Test accounts allowed
- ⚠️ Relaxed password rules for testing

Network Security:
- ⚠️ IP Whitelist optional (for remote testing)
- ✅ HTTPS mandatory
- ⚠️ VPN allowed (for remote testers)

Location Validation:
- ⚠️ Geofencing optional (configurable via admin panel)
- ⚠️ Can accept any location for testing
- ✅ Still logs all coordinates

Biometric Validation:
- ⚠️ Face verification optional (configurable)
- ⚠️ Can use dummy images for testing
- ✅ Still captures and stores photos

Device Security:
- ✅ Device fingerprinting active
- ⚠️ Auto-approve devices (no admin needed)
- ⚠️ Unlimited devices for testing

Rate Limiting:
- ⚠️ Relaxed (10 check-ins per day)
- ⚠️ Higher API limits

Audit & Logging:
- ✅ Basic logging still active
- ✅ Test mode clearly marked in logs
- ⚠️ Shorter retention (30 days)
```

**TESTING MODE INDICATORS:**
```html
<!-- Every page shows: -->
<div class="warning-banner">
  ⚠️ TESTING MODE - NOT FOR PRODUCTION USE
</div>

<!-- Watermark on all screenshots -->
<!-- Different color scheme (orange header) -->
<!-- Test data clearly marked -->
```

---

### **TIER 3: DEVELOPMENT** ⭐
**Use When:** Local development ONLY

**⚠️ STRICT REQUIREMENTS:**
```
LOCALHOST ONLY:
- ✅ 127.0.0.1 or ::1 ONLY
- ✅ Cannot bind to 0.0.0.0 (all interfaces)
- ✅ Auto-reject if accessed remotely
- ✅ Big warning banner
```

**Security Controls (Minimal):**
```
- ⚠️ Mock authentication (bypass available)
- ⚠️ All validations optional
- ⚠️ Extensive debug output
- ⚠️ No rate limiting
- ✅ Still logs actions (for debugging)
```

**Code Enforcement:**
```php
// Development mode enforces localhost
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true) {
    $allowed_ips = ['127.0.0.1', '::1'];
    if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
        http_response_code(403);
        die('
            <h1>403 Forbidden</h1>
            <p>Development mode only accessible from localhost</p>
            <p>Current IP: ' . htmlspecialchars($_SERVER['REMOTE_ADDR']) . '</p>
            <p>Allowed IPs: 127.0.0.1, ::1</p>
        ');
    }
}
```

---

## 🚨 SECURITY VIOLATIONS

### **Automatic Blocks:**

System will automatically block/alert when:

```
🔴 CRITICAL - Immediate Block + Alert:
- SQL injection attempt detected
- XSS payload detected
- Path traversal attempt
- Command injection attempt
- Authentication bypass attempt
- Privilege escalation attempt
- Multiple failed logins (>3 in 5 minutes)
- Attendance from impossible location
- VPN/Proxy detected (production mode)
- Tampered request headers
- Invalid device fingerprint

🟠 HIGH - Alert + Log:
- Unusual access patterns
- Access from new location
- Access from new device
- High request rate
- Failed face verification (>3 times)
- Geofence violation
- Clock drift (>5 minutes)

🟡 MEDIUM - Log Only:
- Failed login (1-2 attempts)
- Invalid input format
- Outdated browser/client
```

### **Response Actions:**

```
CRITICAL Violation:
1. Immediate IP block (1 hour)
2. Session termination
3. Alert security team (email + SMS)
4. Log detailed incident
5. Require admin review before unblock

HIGH Violation:
1. Rate limit (slow down requests)
2. Additional verification required
3. Alert admin dashboard
4. Enhanced logging for 24 hours

MEDIUM Violation:
1. Log event
2. Monitor for pattern
3. No immediate action
```

---

## 📋 PRE-DEPLOYMENT CHECKLIST

### **Phase 1: Environment Setup**

```
Infrastructure:
[ ] Server hardened (CIS benchmarks)
[ ] Firewall configured (only necessary ports)
[ ] SSL certificate installed (A+ rating)
[ ] Reverse proxy configured (Nginx/Apache)
[ ] Database server secured
[ ] Backup system configured
[ ] Monitoring tools installed

Network:
[ ] DMZ configured for web server
[ ] Internal network isolated
[ ] IP whitelist configured
[ ] DDoS protection active
[ ] IDS/IPS configured
```

### **Phase 2: Application Security**

```
Code:
[ ] Security audit completed
[ ] Penetration testing passed
[ ] Code review approved
[ ] Dependency scan (no high/critical vulns)
[ ] OWASP Top 10 mitigated
[ ] Error handling proper (no info leak)
[ ] Debug mode DISABLED

Configuration:
[ ] Production config active
[ ] Secure secrets management
[ ] Database credentials rotated
[ ] API keys secured
[ ] File permissions correct (755/644)
[ ] Sensitive files protected (.env, config)
[ ] Directory listing disabled
```

### **Phase 3: Operational Security**

```
Access Control:
[ ] Admin accounts secured (strong passwords)
[ ] Test accounts removed
[ ] Service accounts minimized
[ ] SSH key-only access
[ ] sudo properly configured

Monitoring:
[ ] Log aggregation configured
[ ] Alerting rules defined
[ ] Dashboard created
[ ] Incident response plan ready
[ ] Contact list updated

Compliance:
[ ] Privacy policy published
[ ] Terms of service agreed
[ ] Data retention policy defined
[ ] User consent mechanism active
[ ] Audit trail verified
```

### **Phase 4: User Communication**

```
Documentation:
[ ] User manual prepared
[ ] FAQ published
[ ] Training completed
[ ] Support process defined

Rollout:
[ ] Pilot group tested (10-20 users)
[ ] Feedback collected
[ ] Issues resolved
[ ] Gradual rollout plan
[ ] Rollback plan ready
```

---

## 🔍 REGULAR SECURITY AUDITS

### **Daily:**
```
- Monitor security logs
- Check failed login attempts
- Review attendance anomalies
- Verify backup completion
```

### **Weekly:**
```
- Review access logs
- Analyze user behavior patterns
- Check system performance
- Update threat intelligence
```

### **Monthly:**
```
- Security patch review & apply
- User access review
- Incident report summary
- Penetration testing (automated)
```

### **Quarterly:**
```
- Full security audit
- Compliance review
- Disaster recovery test
- Security training refresh
```

### **Annually:**
```
- Third-party security assessment
- Certification renewal (ISO 27001)
- Policy review & update
- Major penetration testing
```

---

## 📞 INCIDENT RESPONSE

### **Severity Levels:**

```
P1 - CRITICAL (< 15 minutes response):
- Active attack in progress
- Data breach confirmed
- System compromised
- Ransomware detected

P2 - HIGH (< 1 hour response):
- Multiple failed attacks
- Suspicious activity confirmed
- Unauthorized access attempt
- DDoS attack

P3 - MEDIUM (< 4 hours response):
- Single security violation
- Unusual behavior detected
- Performance degradation
- Minor vulnerability found

P4 - LOW (< 24 hours response):
- Policy violation
- Configuration issue
- User complaint
- General inquiry
```

### **Response Team:**

```
Incident Commander:
- Kepala Bidang TI BKPSDM
- Phone: [NUMBER]
- Email: it-lead@bekasikab.go.id

Security Lead:
- Security Officer
- Phone: [NUMBER]
- Email: security@bekasikab.go.id

Technical Lead:
- Senior Developer
- Phone: [NUMBER]
- Email: dev-lead@bekasikab.go.id

Legal Counsel:
- Legal Department
- Phone: [NUMBER]
- Email: legal@bekasikab.go.id
```

---

## ⚖️ LEGAL & COMPLIANCE

### **Data Protection:**

```
User Data Collected:
- Name, NIP (employee ID)
- Login credentials (hashed)
- IP address
- Device information
- GPS coordinates
- Facial photographs
- Attendance timestamps

Legal Basis:
- Employment contract
- Government regulation
- Legitimate interest (workforce management)

User Rights:
- Right to access data
- Right to correction
- Right to explanation
- Right to complaint
```

### **Data Retention:**

```
Attendance Records: 5 years (minimum)
Audit Logs: 1 year (minimum)
Face Photos: 1 year (encrypted)
Login History: 90 days
Error Logs: 30 days
```

### **Data Sharing:**

```
Internal Only:
- HR Department (attendance reports)
- Finance (payroll calculation)
- Management (analytics)

NO External Sharing without:
- User consent
- Legal requirement
- Court order
```

---

## 🛡️ SECURITY CONTACTS

**For Security Issues:**
```
Email: security@bekasikab.go.id
Phone: [24/7 Hotline]
Ticket: https://security.bekasikab.go.id/report
```

**For Vulnerabilities:**
```
Email: vuln-report@bekasikab.go.id
PGP Key: [Public Key]
Bug Bounty: [If applicable]
```

---

**Document Version:** 1.0  
**Last Updated:** 2025-11-23  
**Next Review:** 2026-02-23  
**Approved By:** [Security Team Lead]
