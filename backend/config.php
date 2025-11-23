<?php
/**
 * Web Attendance System - Configuration
 * BKPSDM Kabupaten Bekasi
 * 
 * SECURITY NOTICE:
 * - Copy this file and save as config.local.php for your environment
 * - NEVER commit config.local.php to version control
 * - Keep credentials secure and rotate regularly
 */

// Error reporting (DISABLE in production!)
if ($_ENV['ENVIRONMENT'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================================================
// SECURITY TIER CONFIGURATION
// ============================================================================
// Choose ONE: 'PRODUCTION', 'TESTING', 'DEVELOPMENT'
define('SECURITY_TIER', 'TESTING'); // ⚠️ CHANGE TO PRODUCTION FOR LIVE USE

// Enforce localhost for DEVELOPMENT mode
if (SECURITY_TIER === 'DEVELOPMENT') {
    $allowed_ips = ['127.0.0.1', '::1'];
    if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowed_ips)) {
        http_response_code(403);
        die('<h1>403 Forbidden</h1><p>Development mode only allowed on localhost</p>');
    }
}

// ============================================================================
// TIER-BASED SECURITY SETTINGS
// ============================================================================

switch (SECURITY_TIER) {
    case 'PRODUCTION':
        // ⭐⭐⭐⭐⭐ FULL SECURITY
        define('REQUIRE_2FA', true);
        define('REQUIRE_IP_WHITELIST', true);
        define('REQUIRE_DEVICE_REGISTRATION', true);
        define('REQUIRE_DEVICE_APPROVAL', true);
        define('REQUIRE_FACE_VERIFICATION', true);
        define('REQUIRE_LIVENESS_DETECTION', true);
        define('REQUIRE_GEOFENCING', true);
        define('SESSION_TIMEOUT', 300); // 5 minutes
        define('MAX_LOGIN_ATTEMPTS', 3);
        define('MAX_DAILY_CHECKINS', 2);
        define('ALLOW_VPN', false);
        define('ALLOW_PROXY', false);
        define('ENABLE_WAF', true);
        define('ENABLE_RATE_LIMITING', true);
        define('LOG_LEVEL', 'INFO');
        break;
        
    case 'TESTING':
        // ⭐⭐⭐ REDUCED SECURITY FOR TESTING
        // ⚠️ CONTROLLED ENVIRONMENT ONLY
        define('REQUIRE_2FA', false); // Disabled for easier testing
        define('REQUIRE_IP_WHITELIST', false); // Allow remote testing
        define('REQUIRE_DEVICE_REGISTRATION', true);
        define('REQUIRE_DEVICE_APPROVAL', false); // Auto-approve for testing
        define('REQUIRE_FACE_VERIFICATION', false); // Optional, configurable per user
        define('REQUIRE_LIVENESS_DETECTION', false);
        define('REQUIRE_GEOFENCING', false); // Optional, configurable
        define('SESSION_TIMEOUT', 1800); // 30 minutes
        define('MAX_LOGIN_ATTEMPTS', 10);
        define('MAX_DAILY_CHECKINS', 10);
        define('ALLOW_VPN', true);
        define('ALLOW_PROXY', true);
        define('ENABLE_WAF', true);
        define('ENABLE_RATE_LIMITING', false);
        define('LOG_LEVEL', 'DEBUG');
        break;
        
    case 'DEVELOPMENT':
        // ⭐ MINIMAL SECURITY - LOCALHOST ONLY
        define('REQUIRE_2FA', false);
        define('REQUIRE_IP_WHITELIST', false);
        define('REQUIRE_DEVICE_REGISTRATION', false);
        define('REQUIRE_DEVICE_APPROVAL', false);
        define('REQUIRE_FACE_VERIFICATION', false);
        define('REQUIRE_LIVENESS_DETECTION', false);
        define('REQUIRE_GEOFENCING', false);
        define('SESSION_TIMEOUT', 3600); // 1 hour
        define('MAX_LOGIN_ATTEMPTS', 999);
        define('MAX_DAILY_CHECKINS', 999);
        define('ALLOW_VPN', true);
        define('ALLOW_PROXY', true);
        define('ENABLE_WAF', false);
        define('ENABLE_RATE_LIMITING', false);
        define('LOG_LEVEL', 'DEBUG');
        break;
}

// ============================================================================
// DATABASE CONFIGURATION
// ============================================================================

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'bkpsdm_attendance');
define('DB_USER', $_ENV['DB_USER'] ?? 'attendance_user');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'CHANGE_THIS_PASSWORD');
define('DB_CHARSET', 'utf8mb4');

// ============================================================================
// API CONFIGURATION
// ============================================================================

// Base API URL (should match mobile app API)
define('API_BASE_URL', 'https://bisma.bekasikab.go.id/api');

// API endpoints
define('API_LOGIN', API_BASE_URL . '/auth/login');
define('API_VERIFY_OTP', API_BASE_URL . '/auth/verify-otp');
define('API_CHECKIN', API_BASE_URL . '/attendance/checkin');
define('API_CHECKOUT', API_BASE_URL . '/attendance/checkout');
define('API_HISTORY', API_BASE_URL . '/attendance/history');
define('API_VALIDATE_FACE', API_BASE_URL . '/validate/face');
define('API_VALIDATE_LOCATION', API_BASE_URL . '/validate/location');

// ============================================================================
// GEOFENCING CONFIGURATION
// ============================================================================

// Office locations (add all office locations here)
$OFFICE_LOCATIONS = [
    [
        'name' => 'BKPSDM Kantor Utama',
        'address' => 'Jl. [Alamat Kantor]',
        'latitude' => -6.2345678,  // ⚠️ CHANGE TO ACTUAL COORDINATES
        'longitude' => 107.1234567, // ⚠️ CHANGE TO ACTUAL COORDINATES
        'radius' => 100 // meters
    ],
    [
        'name' => 'BKPSDM Cabang',
        'address' => 'Jl. [Alamat Cabang]',
        'latitude' => -6.3456789,
        'longitude' => 107.2345678,
        'radius' => 100
    ]
];

define('OFFICE_LOCATIONS', json_encode($OFFICE_LOCATIONS));

// ============================================================================
// IP WHITELIST CONFIGURATION
// ============================================================================

// Allowed IP addresses/ranges (CIDR notation supported)
$IP_WHITELIST = [
    '192.168.1.0/24',      // Office LAN
    '10.0.0.0/8',          // Internal network
    '203.xxx.xxx.xxx',     // Office public IP (⚠️ CHANGE THIS)
];

define('IP_WHITELIST', json_encode($IP_WHITELIST));

// ============================================================================
// FACE VERIFICATION CONFIGURATION
// ============================================================================

define('FACE_SIMILARITY_THRESHOLD', 0.75); // 75% similarity required
define('FACE_MIN_QUALITY', 0.6); // Minimum image quality
define('FACE_MAX_AGE_DAYS', 30); // Re-register face every 30 days
define('FACE_LIVENESS_REQUIRED', REQUIRE_LIVENESS_DETECTION);

// Face verification API (if using external service)
define('FACE_API_URL', 'https://api.face-recognition-service.com/verify');
define('FACE_API_KEY', $_ENV['FACE_API_KEY'] ?? '');

// ============================================================================
// SESSION CONFIGURATION
// ============================================================================

define('SESSION_NAME', 'BKPSDM_ATTENDANCE_SESSION');
define('SESSION_COOKIE_LIFETIME', SESSION_TIMEOUT);
define('SESSION_COOKIE_PATH', '/');
define('SESSION_COOKIE_DOMAIN', $_SERVER['HTTP_HOST'] ?? '');
define('SESSION_COOKIE_SECURE', true); // HTTPS only
define('SESSION_COOKIE_HTTPONLY', true); // No JavaScript access
define('SESSION_COOKIE_SAMESITE', 'Strict');

// ============================================================================
// SECURITY HEADERS
// ============================================================================

$SECURITY_HEADERS = [
    'X-Frame-Options' => 'DENY',
    'X-Content-Type-Options' => 'nosniff',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Permissions-Policy' => 'geolocation=(self), camera=(self), microphone=()',
    'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;",
];

if (SECURITY_TIER === 'PRODUCTION') {
    $SECURITY_HEADERS['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
}

define('SECURITY_HEADERS', json_encode($SECURITY_HEADERS));

// ============================================================================
// LOGGING CONFIGURATION
// ============================================================================

define('LOG_DIR', __DIR__ . '/../logs');
define('LOG_FILE_ACCESS', LOG_DIR . '/access.log');
define('LOG_FILE_ERROR', LOG_DIR . '/error.log');
define('LOG_FILE_AUTH', LOG_DIR . '/auth.log');
define('LOG_FILE_ATTENDANCE', LOG_DIR . '/attendance.log');
define('LOG_FILE_SECURITY', LOG_DIR . '/security.log');
define('LOG_FILE_AUDIT', LOG_DIR . '/audit.log');

// Log rotation
define('LOG_MAX_SIZE', 10485760); // 10MB
define('LOG_MAX_FILES', 10);

// ============================================================================
// NOTIFICATION CONFIGURATION
// ============================================================================

// Email settings
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com');
define('MAIL_PORT', $_ENV['MAIL_PORT'] ?? 587);
define('MAIL_USER', $_ENV['MAIL_USER'] ?? 'noreply@bekasikab.go.id');
define('MAIL_PASS', $_ENV['MAIL_PASS'] ?? '');
define('MAIL_FROM', 'noreply@bekasikab.go.id');
define('MAIL_FROM_NAME', 'BKPSDM Attendance System');

// SMS settings (for OTP)
define('SMS_PROVIDER', 'twilio'); // 'twilio', 'nexmo', or custom
define('SMS_API_KEY', $_ENV['SMS_API_KEY'] ?? '');
define('SMS_API_SECRET', $_ENV['SMS_API_SECRET'] ?? '');
define('SMS_SENDER', 'BKPSDM');

// Alert settings
define('ALERT_EMAIL', 'security@bekasikab.go.id');
define('ALERT_PHONE', '+62xxx'); // For critical alerts

// ============================================================================
// RATE LIMITING CONFIGURATION
// ============================================================================

if (ENABLE_RATE_LIMITING) {
    define('RATE_LIMIT_LOGIN', 5); // per hour
    define('RATE_LIMIT_API', 60); // per minute
    define('RATE_LIMIT_CHECKIN', 3); // per hour
}

// ============================================================================
// FILE UPLOAD CONFIGURATION
// ============================================================================

define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Face photo storage
define('FACE_PHOTO_DIR', UPLOAD_DIR . '/faces');
define('ATTENDANCE_PHOTO_DIR', UPLOAD_DIR . '/attendance');

// ============================================================================
// ENCRYPTION CONFIGURATION
// ============================================================================

define('ENCRYPTION_METHOD', 'AES-256-CBC');
define('ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY'] ?? base64_encode(random_bytes(32)));
define('HASH_ALGORITHM', 'sha256');

// Password hashing
define('PASSWORD_HASH_ALGORITHM', PASSWORD_ARGON2ID);
define('PASSWORD_HASH_OPTIONS', [
    'memory_cost' => 2048,
    'time_cost' => 4,
    'threads' => 3
]);

// ============================================================================
// TIMEZONE & LOCALE
// ============================================================================

date_default_timezone_set('Asia/Jakarta');
define('LOCALE', 'id_ID');
setlocale(LC_TIME, 'id_ID.UTF-8');

// ============================================================================
// APPLICATION SETTINGS
// ============================================================================

define('APP_NAME', 'BKPSDM Attendance System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', SECURITY_TIER);

// Working hours
define('WORK_START_TIME', '07:30:00');
define('WORK_END_TIME', '16:00:00');
define('LATE_THRESHOLD_MINUTES', 15); // Late if check-in after 07:45

// Grace period for early/late checkout
define('CHECKOUT_EARLY_GRACE_MINUTES', 15);
define('CHECKOUT_LATE_GRACE_MINUTES', 60);

// ============================================================================
// VISUAL INDICATORS
// ============================================================================

// Show warning banner in non-production modes
if (SECURITY_TIER !== 'PRODUCTION') {
    define('SHOW_WARNING_BANNER', true);
    define('WARNING_MESSAGE', '⚠️ ' . SECURITY_TIER . ' MODE - NOT FOR PRODUCTION USE');
    define('WARNING_COLOR', SECURITY_TIER === 'TESTING' ? 'orange' : 'red');
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Check if current IP is in whitelist
 */
function isIPWhitelisted($ip = null) {
    if (!REQUIRE_IP_WHITELIST) return true;
    
    $ip = $ip ?? $_SERVER['REMOTE_ADDR'];
    $whitelist = json_decode(IP_WHITELIST, true);
    
    foreach ($whitelist as $range) {
        if (ipInRange($ip, $range)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if IP is in CIDR range
 */
function ipInRange($ip, $range) {
    if (strpos($range, '/') === false) {
        $range .= '/32';
    }
    
    list($subnet, $bits) = explode('/', $range);
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet &= $mask;
    
    return ($ip & $mask) == $subnet;
}

/**
 * Calculate distance between two GPS coordinates (Haversine formula)
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meters
    
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    
    $latDelta = $lat2 - $lat1;
    $lonDelta = $lon2 - $lon1;
    
    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos($lat1) * cos($lat2) *
         sin($lonDelta / 2) * sin($lonDelta / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c; // distance in meters
}

/**
 * Check if location is within office geofence
 */
function isWithinOfficeArea($latitude, $longitude) {
    if (!REQUIRE_GEOFENCING) return [
        'valid' => true,
        'message' => 'Geofencing disabled',
        'office' => 'N/A'
    ];
    
    $offices = json_decode(OFFICE_LOCATIONS, true);
    
    foreach ($offices as $office) {
        $distance = calculateDistance(
            $latitude,
            $longitude,
            $office['latitude'],
            $office['longitude']
        );
        
        if ($distance <= $office['radius']) {
            return [
                'valid' => true,
                'office' => $office['name'],
                'distance' => round($distance, 2),
                'message' => 'Within office area'
            ];
        }
    }
    
    return [
        'valid' => false,
        'message' => 'Outside office area',
        'nearest_office' => $offices[0]['name'] ?? 'Unknown'
    ];
}

/**
 * Log security event
 */
function logSecurityEvent($event, $severity = 'INFO', $details = []) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'severity' => $severity,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'details' => $details
    ];
    
    $logFile = LOG_FILE_SECURITY;
    file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
    
    // Alert on critical events
    if ($severity === 'CRITICAL' && SECURITY_TIER === 'PRODUCTION') {
        // Send alert email/SMS
        sendSecurityAlert($logEntry);
    }
}

/**
 * Send security alert (placeholder)
 */
function sendSecurityAlert($details) {
    // Implement email/SMS alert here
    error_log('SECURITY ALERT: ' . json_encode($details));
}

// ============================================================================
// INITIALIZE
// ============================================================================

// Create required directories
$requiredDirs = [LOG_DIR, UPLOAD_DIR, FACE_PHOTO_DIR, ATTENDANCE_PHOTO_DIR];
foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Set security headers
foreach (json_decode(SECURITY_HEADERS, true) as $header => $value) {
    header("$header: $value");
}

// Start session with secure settings
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');
session_name(SESSION_NAME);
session_start();

// Log configuration loaded
logSecurityEvent('Configuration loaded', 'INFO', [
    'security_tier' => SECURITY_TIER,
    'server' => $_SERVER['SERVER_NAME'] ?? 'Unknown'
]);

// Display security tier warning
if (defined('SHOW_WARNING_BANNER') && SHOW_WARNING_BANNER) {
    define('SECURITY_WARNING_HTML', '
        <div style="
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: ' . WARNING_COLOR . ';
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            z-index: 9999;
            border-bottom: 3px solid darkred;
        ">
            ' . WARNING_MESSAGE . '
        </div>
        <div style="height: 45px;"></div>
    ');
}

?>
