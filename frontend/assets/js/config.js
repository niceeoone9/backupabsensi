/**
 * Configuration
 */

const CONFIG = {
    // API Base URL - will be set dynamically
    API_BASE_URL: window.location.origin,
    
    // Use API Bridge to connect with mobile backend
    USE_API_BRIDGE: true,  // Set to true to connect with mobile API
    
    // API Endpoints
    API: {
        // API Bridge endpoints (connects to mobile backend)
        LOGIN: '/api_bridge.php/api/auth/login',
        LOGOUT: '/demo.php/api/auth/logout',
        SESSION: '/demo.php/api/auth/session',
        CHECKIN: '/api_bridge.php/api/attendance/checkin',
        CHECKOUT: '/api_bridge.php/api/attendance/checkout',
        TODAY: '/demo.php/api/attendance/today',
        HISTORY: '/demo.php/api/attendance/history'
    },
    
    // Security Settings (Demo Mode)
    SECURITY: {
        REQUIRE_GEOFENCING: false, // Set to false for demo
        REQUIRE_FACE_VERIFICATION: false, // Set to false for demo
        SESSION_TIMEOUT: 1800000, // 30 minutes
    },
    
    // Office Locations
    OFFICE_LOCATIONS: [
        {
            name: 'BKPSDM Kantor Utama',
            latitude: -6.2345678,
            longitude: 107.1234567,
            radius: 100 // meters
        }
    ],
    
    // Demo Mode
    DEMO_MODE: true,
    DEMO_CREDENTIALS: {
        nip: 'demo',
        password: 'demo123'
    }
};
