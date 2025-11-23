/**
 * Configuration
 */

const CONFIG = {
    // API Base URL - will be set dynamically
    API_BASE_URL: window.location.origin,
    
    // Use Netlify Functions for API proxy (connects to mobile backend)
    USE_NETLIFY_FUNCTIONS: true,
    
    // API Endpoints
    API: {
        // Netlify Functions endpoints (connects to mobile backend API)
        LOGIN: '/.netlify/functions/api-proxy/login',
        LOGOUT: '/demo.php/api/auth/logout',
        SESSION: '/demo.php/api/auth/session',
        CHECKIN: '/.netlify/functions/api-proxy/checkin',
        CHECKOUT: '/.netlify/functions/api-proxy/checkout',
        TODAY: '/.netlify/functions/api-proxy/today',
        HISTORY: '/.netlify/functions/api-proxy/history'
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
    DEMO_MODE: false,  // Set to false to use real API via Netlify Functions
    DEMO_CREDENTIALS: {
        nip: 'demo',
        password: 'demo123'
    }
};
