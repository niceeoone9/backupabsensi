<?php
/**
 * Demo Backend for Web Attendance System
 * Simplified version untuk preview/testing
 */

// Enable CORS for testing
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple session
session_start();

// Get request data
$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Route handling
if (strpos($request_uri, '/api/auth/login') !== false && $method === 'POST') {
    handleLogin($input);
} elseif (strpos($request_uri, '/api/auth/logout') !== false && $method === 'POST') {
    handleLogout();
} elseif (strpos($request_uri, '/api/auth/session') !== false && $method === 'GET') {
    handleSession();
} elseif (strpos($request_uri, '/api/attendance/checkin') !== false && $method === 'POST') {
    handleCheckin($input);
} elseif (strpos($request_uri, '/api/attendance/checkout') !== false && $method === 'POST') {
    handleCheckout($input);
} elseif (strpos($request_uri, '/api/attendance/today') !== false && $method === 'GET') {
    handleToday();
} elseif (strpos($request_uri, '/api/attendance/history') !== false && $method === 'GET') {
    handleHistory();
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}

// ============================================================================
// HANDLERS
// ============================================================================

function handleLogin($input) {
    $nip = $input['nip'] ?? '';
    $password = $input['password'] ?? '';
    
    // Demo validation - accept any NIP with password "demo123"
    if (!empty($nip) && $password === 'demo123') {
        $_SESSION['logged_in'] = true;
        $_SESSION['user'] = [
            'nip' => $nip,
            'nama' => 'Demo User ' . substr($nip, -3),
            'jabatan' => 'Staff BKPSDM',
            'unit_kerja' => 'Bidang Kepegawaian',
            'email' => $nip . '@bekasikab.go.id',
            'phone' => '08123456789'
        ];
        
        logActivity('Login successful', ['nip' => $nip]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => $_SESSION['user']
        ]);
    } else {
        logActivity('Login failed', ['nip' => $nip]);
        
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'NIP atau password salah. Demo password: demo123'
        ]);
    }
}

function handleLogout() {
    if (isset($_SESSION['user'])) {
        logActivity('Logout', ['nip' => $_SESSION['user']['nip']]);
    }
    
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout berhasil'
    ]);
}

function handleSession() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => $_SESSION['user']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'logged_in' => false
        ]);
    }
}

function handleCheckin($input) {
    if (!isLoggedIn()) {
        unauthorized();
        return;
    }
    
    $latitude = $input['latitude'] ?? '';
    $longitude = $input['longitude'] ?? '';
    $photo = $input['photo'] ?? '';
    $timestamp = date('Y-m-d H:i:s');
    
    // Simulate geofencing check
    $geofence_result = checkGeofence($latitude, $longitude);
    
    // Store in session (in real app, store in database)
    $_SESSION['attendance_today'] = [
        'checkin_time' => $timestamp,
        'checkin_lat' => $latitude,
        'checkin_lng' => $longitude,
        'checkin_photo' => substr($photo, 0, 100) . '...', // Truncate
        'checkin_location' => $geofence_result['location'],
        'checkout_time' => null
    ];
    
    logActivity('Check-in', [
        'nip' => $_SESSION['user']['nip'],
        'time' => $timestamp,
        'location' => "{$latitude},{$longitude}",
        'geofence' => $geofence_result['valid'] ? 'valid' : 'invalid'
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Check-in berhasil!',
        'data' => [
            'timestamp' => $timestamp,
            'geofence_valid' => $geofence_result['valid'],
            'location' => $geofence_result['location']
        ]
    ]);
}

function handleCheckout($input) {
    if (!isLoggedIn()) {
        unauthorized();
        return;
    }
    
    if (!isset($_SESSION['attendance_today']) || $_SESSION['attendance_today']['checkout_time']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Belum check-in atau sudah check-out'
        ]);
        return;
    }
    
    $latitude = $input['latitude'] ?? '';
    $longitude = $input['longitude'] ?? '';
    $photo = $input['photo'] ?? '';
    $timestamp = date('Y-m-d H:i:s');
    
    $_SESSION['attendance_today']['checkout_time'] = $timestamp;
    $_SESSION['attendance_today']['checkout_lat'] = $latitude;
    $_SESSION['attendance_today']['checkout_lng'] = $longitude;
    $_SESSION['attendance_today']['checkout_photo'] = substr($photo, 0, 100) . '...';
    
    logActivity('Check-out', [
        'nip' => $_SESSION['user']['nip'],
        'time' => $timestamp,
        'location' => "{$latitude},{$longitude}"
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Check-out berhasil!',
        'data' => [
            'timestamp' => $timestamp
        ]
    ]);
}

function handleToday() {
    if (!isLoggedIn()) {
        unauthorized();
        return;
    }
    
    $today = $_SESSION['attendance_today'] ?? null;
    
    echo json_encode([
        'success' => true,
        'data' => $today
    ]);
}

function handleHistory() {
    if (!isLoggedIn()) {
        unauthorized();
        return;
    }
    
    // Demo data - generate last 10 days
    $history = [];
    for ($i = 0; $i < 10; $i++) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $checkin = date('H:i:s', strtotime("08:" . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ":00"));
        $checkout = date('H:i:s', strtotime("16:" . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ":00"));
        
        $history[] = [
            'date' => $date,
            'checkin_time' => $checkin,
            'checkout_time' => $checkout,
            'status' => rand(0, 10) > 8 ? 'late' : 'on_time',
            'location' => 'BKPSDM Kantor Utama'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $history
    ]);
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
}

function unauthorized() {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
}

function checkGeofence($lat, $lng) {
    // Demo office location (adjust to actual)
    $office_lat = -6.2345678;
    $office_lng = 107.1234567;
    $radius = 100; // meters
    
    if (empty($lat) || empty($lng)) {
        return [
            'valid' => false,
            'location' => 'Location not available'
        ];
    }
    
    $distance = calculateDistance($lat, $lng, $office_lat, $office_lng);
    
    if ($distance <= $radius) {
        return [
            'valid' => true,
            'location' => 'BKPSDM Kantor Utama',
            'distance' => round($distance, 2)
        ];
    }
    
    return [
        'valid' => false,
        'location' => 'Outside office area',
        'distance' => round($distance, 2)
    ];
}

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
    
    return $earthRadius * $c;
}

function logActivity($event, $data) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'data' => $data
    ];
    
    $log_file = __DIR__ . '/../../logs/demo_activity.log';
    
    // Create logs directory if not exists
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND);
}

?>
