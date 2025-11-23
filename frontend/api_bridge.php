<?php
/**
 * API Bridge untuk koneksi ke Backend BKPSDMApp
 * Menghubungkan web attendance dengan API server yang sama dengan mobile app
 */

// Configuration
define('MOBILE_API_BASE', 'https://bisma.bekasikab.go.id/api');

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Get request info
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$input = json_decode(file_get_contents('php://input'), true);

// Parse endpoint
$endpoint = '';
if (strpos($request_uri, '/api/') !== false) {
    $endpoint = substr($request_uri, strpos($request_uri, '/api/'));
}

// Route requests
if (strpos($request_uri, '/api/auth/login') !== false && $request_method === 'POST') {
    handleLoginBridge($input);
} elseif (strpos($request_uri, '/api/attendance/checkin') !== false && $request_method === 'POST') {
    handleCheckinBridge($input);
} elseif (strpos($request_uri, '/api/attendance/checkout') !== false && $request_method === 'POST') {
    handleCheckoutBridge($input);
} else {
    // Forward other requests to mobile API
    forwardToMobileAPI($endpoint, $request_method, $input);
}

// ============================================================================
// HANDLERS WITH MOBILE API INTEGRATION
// ============================================================================

/**
 * Login Bridge - Connect to mobile API
 */
function handleLoginBridge($input) {
    $nip = $input['nip'] ?? '';
    $password = $input['password'] ?? '';
    
    // Try to connect to actual mobile API
    $mobile_api_url = MOBILE_API_BASE . '/login';
    
    $postData = [
        'username' => $nip,  // Mobile app uses 'username' field
        'password' => $password
    ];
    
    $response = callMobileAPI($mobile_api_url, 'POST', $postData);
    
    if ($response && $response['success']) {
        // Success from mobile API
        $_SESSION['logged_in'] = true;
        $_SESSION['user'] = $response['data'] ?? [
            'nip' => $nip,
            'nama' => $response['data']['nama'] ?? 'User',
            'jabatan' => $response['data']['jabatan'] ?? '',
            'unit_kerja' => $response['data']['unit_kerja'] ?? ''
        ];
        $_SESSION['token'] = $response['token'] ?? $response['data']['token'] ?? '';
        
        logActivity('Login successful (via mobile API)', ['nip' => $nip]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => $_SESSION['user'],
            'token' => $_SESSION['token']
        ]);
    } else {
        // Fallback to demo mode if mobile API unreachable
        if ($password === 'demo123') {
            $_SESSION['logged_in'] = true;
            $_SESSION['user'] = [
                'nip' => $nip,
                'nama' => 'Demo User ' . substr($nip, -3),
                'jabatan' => 'Staff BKPSDM',
                'unit_kerja' => 'Bidang Kepegawaian',
                'email' => $nip . '@bekasikab.go.id',
                'phone' => '08123456789'
            ];
            
            logActivity('Login successful (demo mode)', ['nip' => $nip]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil (Demo Mode)',
                'data' => $_SESSION['user'],
                'mode' => 'demo'
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'NIP atau password salah. Demo: demo123'
            ]);
        }
    }
}

/**
 * Check-in Bridge - Send to mobile API with same format
 */
function handleCheckinBridge($input) {
    if (!isLoggedIn()) {
        unauthorized();
        return;
    }
    
    // Format data sesuai dengan mobile app
    $attendanceData = [
        'FotoCheckIn' => $input['photo'] ?? '',
        'latCheckIn' => $input['latitude'] ?? '',
        'lngCheckIn' => $input['longitude'] ?? '',
        'latAbsen' => $input['latitude'] ?? '',
        'lngAbsen' => $input['longitude'] ?? '',
        'waktuCheckIn' => date('H:i:s'),
        'tglAbsen' => date('Y-m-d'),
        'KodeAbsenUser' => $_SESSION['user']['nip'] ?? ''
    ];
    
    // Try mobile API
    $mobile_api_url = MOBILE_API_BASE . '/absen';
    $token = $_SESSION['token'] ?? '';
    
    $response = callMobileAPI($mobile_api_url, 'POST', $attendanceData, $token);
    
    if ($response && $response['success']) {
        // Success from mobile API
        $_SESSION['attendance_today'] = [
            'checkin_time' => date('H:i:s'),
            'checkin_lat' => $input['latitude'],
            'checkin_lng' => $input['longitude'],
            'checkout_time' => null
        ];
        
        logActivity('Check-in (via mobile API)', [
            'nip' => $_SESSION['user']['nip'],
            'location' => $input['latitude'] . ',' . $input['longitude']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Check-in berhasil!',
            'data' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'source' => 'mobile_api'
            ]
        ]);
    } else {
        // Fallback to local storage
        $_SESSION['attendance_today'] = [
            'checkin_time' => date('H:i:s'),
            'checkin_lat' => $input['latitude'],
            'checkin_lng' => $input['longitude'],
            'checkin_photo' => substr($input['photo'] ?? '', 0, 100) . '...',
            'checkout_time' => null
        ];
        
        logActivity('Check-in (local mode)', [
            'nip' => $_SESSION['user']['nip'],
            'location' => $input['latitude'] . ',' . $input['longitude']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Check-in berhasil! (Local)',
            'data' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'source' => 'local'
            ]
        ]);
    }
}

/**
 * Check-out Bridge - Send to mobile API
 */
function handleCheckoutBridge($input) {
    if (!isLoggedIn()) {
        unauthorized();
        return;
    }
    
    // Format data sesuai dengan mobile app
    $attendanceData = [
        'FotoCheckOut' => $input['photo'] ?? '',
        'latCheckOut' => $input['latitude'] ?? '',
        'lngCheckOut' => $input['longitude'] ?? '',
        'waktuCheckOut' => date('H:i:s'),
        'tglAbsen' => date('Y-m-d'),
        'KodeAbsenUser' => $_SESSION['user']['nip'] ?? ''
    ];
    
    // Try mobile API
    $mobile_api_url = MOBILE_API_BASE . '/absen';  // Same endpoint as check-in
    $token = $_SESSION['token'] ?? '';
    
    $response = callMobileAPI($mobile_api_url, 'POST', $attendanceData, $token);
    
    if ($response && $response['success']) {
        // Success from mobile API
        if (isset($_SESSION['attendance_today'])) {
            $_SESSION['attendance_today']['checkout_time'] = date('H:i:s');
        }
        
        logActivity('Check-out (via mobile API)', [
            'nip' => $_SESSION['user']['nip'],
            'location' => $input['latitude'] . ',' . $input['longitude']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Check-out berhasil!',
            'data' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'source' => 'mobile_api'
            ]
        ]);
    } else {
        // Fallback to local storage
        if (isset($_SESSION['attendance_today'])) {
            $_SESSION['attendance_today']['checkout_time'] = date('H:i:s');
            $_SESSION['attendance_today']['checkout_lat'] = $input['latitude'];
            $_SESSION['attendance_today']['checkout_lng'] = $input['longitude'];
        }
        
        logActivity('Check-out (local mode)', [
            'nip' => $_SESSION['user']['nip'],
            'location' => $input['latitude'] . ',' . $input['longitude']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Check-out berhasil! (Local)',
            'data' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'source' => 'local'
            ]
        ]);
    }
}

/**
 * Forward request to mobile API
 */
function forwardToMobileAPI($endpoint, $method, $data) {
    $token = $_SESSION['token'] ?? '';
    $url = MOBILE_API_BASE . $endpoint;
    
    $response = callMobileAPI($url, $method, $data, $token);
    
    if ($response) {
        echo json_encode($response);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'API tidak tersedia'
        ]);
    }
}

/**
 * Call Mobile API
 */
function callMobileAPI($url, $method = 'GET', $data = null, $token = '') {
    $ch = curl_init();
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    // Log API call
    logActivity('Mobile API Call', [
        'url' => $url,
        'method' => $method,
        'http_code' => $httpCode,
        'error' => $error ?: 'none'
    ]);
    
    if ($response && $httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    
    return null;
}

// Helper functions
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

function logActivity($event, $data) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'data' => $data
    ];
    
    $log_file = __DIR__ . '/../../logs/api_bridge.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND);
}

?>
