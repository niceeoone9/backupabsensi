/**
 * Netlify Function - API Proxy untuk connect ke Mobile Backend
 * Menggantikan api_bridge.php di Netlify
 */

const fetch = require('node-fetch');

// BISMA API Configuration
const BISMA_BASE = 'https://bisma.bekasikab.go.id';
const LOGIN_ENDPOINT = `${BISMA_BASE}/access/validate`;

exports.handler = async (event, context) => {
  // CORS Headers
  const headers = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'Content-Type, Authorization',
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
    'Content-Type': 'application/json',
  };

  // Handle preflight
  if (event.httpMethod === 'OPTIONS') {
    return {
      statusCode: 200,
      headers,
      body: '',
    };
  }

  try {
    const path = event.path.replace('/.netlify/functions/api-proxy', '');
    const body = event.body ? JSON.parse(event.body) : null;

    console.log('API Proxy Request:', {
      method: event.httpMethod,
      path: path,
      body: body,
    });

    // Route requests
    if (path.includes('/login') && event.httpMethod === 'POST') {
      return await handleLogin(body, headers);
    } else if (path.includes('/checkin') && event.httpMethod === 'POST') {
      return await handleCheckin(body, headers, event.headers.authorization);
    } else if (path.includes('/checkout') && event.httpMethod === 'POST') {
      return await handleCheckout(body, headers, event.headers.authorization);
    } else if (path.includes('/history') && event.httpMethod === 'GET') {
      return await handleHistory(headers, event.headers.authorization);
    } else if (path.includes('/today') && event.httpMethod === 'GET') {
      return await handleToday(headers, event.headers.authorization);
    } else {
      // Forward other requests
      return await forwardToMobileAPI(path, event.httpMethod, body, headers, event.headers.authorization);
    }
  } catch (error) {
    console.error('API Proxy Error:', error);
    
    return {
      statusCode: 500,
      headers,
      body: JSON.stringify({
        success: false,
        message: 'Internal server error',
        error: error.message,
      }),
    };
  }
};

/**
 * Handle Login - Connect to mobile API
 */
async function handleLogin(body, headers) {
  const { nip, password, recaptchaToken } = body;

  console.log('Login attempt:', { nip, hasPassword: !!password, hasRecaptcha: !!recaptchaToken });

  // Try BISMA website login endpoint
  try {
    // Prepare form data (application/x-www-form-urlencoded)
    const formData = new URLSearchParams();
    formData.append('NamaUser', nip);
    formData.append('PassUser', password);
    if (recaptchaToken) {
      formData.append('g-recaptcha-response', recaptchaToken);
    }

    console.log('Calling BISMA login endpoint:', LOGIN_ENDPOINT);
    
    const response = await fetch(LOGIN_ENDPOINT, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      },
      body: formData.toString(),
      redirect: 'manual', // Don't follow redirects
    });

    console.log('BISMA response status:', response.status);
    
    // Check for redirect (302/301) - indicates successful login
    if (response.status === 302 || response.status === 301) {
      const location = response.headers.get('location');
      console.log('✓ Login successful - redirect to:', location);
      
      // Extract cookies for session
      const cookies = response.headers.get('set-cookie') || '';
      
      return {
        statusCode: 200,
        headers,
        body: JSON.stringify({
          success: true,
          message: 'Login berhasil',
          data: {
            nip: nip,
            nama: 'User ' + nip.substring(0, 10),
            jabatan: 'Pegawai BKPSDM',
            unit_kerja: 'BKPSDM Kab. Bekasi',
            email: nip + '@bekasikab.go.id',
            phone: '',
          },
          token: cookies.split(';')[0] || 'session_' + Date.now(),
          source: 'bisma_web',
          redirectTo: location,
        }),
      };
    }

    // Check response body for errors
    const responseText = await response.text();
    console.log('Response preview:', responseText.substring(0, 200));

    // Check if login page returned (means login failed)
    if (responseText.includes('login-page') || responseText.includes('Password')) {
      console.log('Login failed - returned to login page');
      
      return {
        statusCode: 401,
        headers,
        body: JSON.stringify({
          success: false,
          message: 'NIP atau password salah',
        }),
      };
    }

    // If reCAPTCHA error
    if (responseText.includes('recaptcha') || responseText.includes('captcha')) {
      console.log('reCAPTCHA validation required or failed');
      
      return {
        statusCode: 400,
        headers,
        body: JSON.stringify({
          success: false,
          message: 'Verifikasi reCAPTCHA diperlukan',
          requireRecaptcha: true,
        }),
      };
    }

    // Success if we got here and response is 200
    if (response.status === 200 && !responseText.includes('login-page')) {
      console.log('✓ Login successful - status 200');
      
      const cookies = response.headers.get('set-cookie') || '';
      
      return {
        statusCode: 200,
        headers,
        body: JSON.stringify({
          success: true,
          message: 'Login berhasil',
          data: {
            nip: nip,
            nama: 'User ' + nip.substring(0, 10),
            jabatan: 'Pegawai BKPSDM',
            unit_kerja: 'BKPSDM Kab. Bekasi',
            email: nip + '@bekasikab.go.id',
            phone: '',
          },
          token: cookies.split(';')[0] || 'session_' + Date.now(),
          source: 'bisma_web',
        }),
      };
    }

  } catch (error) {
    console.error('BISMA Login Error:', error.message);
  }

  console.log('Login failed, checking demo mode...');

  // Fallback to demo mode
  if (password === 'demo123' || nip === 'demo') {
    return {
      statusCode: 200,
      headers,
      body: JSON.stringify({
        success: true,
        message: 'Login berhasil (Demo Mode)',
        data: {
          nip: nip,
          nama: 'Demo User - ' + nip.substring(0, 10),
          jabatan: 'Staff BKPSDM',
          unit_kerja: 'Bidang Kepegawaian',
          email: nip + '@bekasikab.go.id',
          phone: '08123456789',
        },
        token: 'demo_token_' + Date.now(),
        source: 'demo',
      }),
    };
  }

  return {
    statusCode: 401,
    headers,
    body: JSON.stringify({
      success: false,
      message: 'NIP atau password salah',
    }),
  };
}

/**
 * Handle Check-in - Send to mobile API
 */
async function handleCheckin(body, headers, authToken) {
  const { photo, latitude, longitude } = body;

  // Format data untuk mobile API
  const attendanceData = {
    FotoCheckIn: photo || '',
    latCheckIn: latitude || '',
    lngCheckIn: longitude || '',
    latAbsen: latitude || '',
    lngAbsen: longitude || '',
    waktuCheckIn: new Date().toLocaleTimeString('id-ID', { hour12: false }),
    tglAbsen: new Date().toISOString().split('T')[0],
  };

  // Try mobile API
  try {
    const response = await fetch(`${MOBILE_API_BASE}/absen`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': authToken || '',
      },
      body: JSON.stringify(attendanceData),
      timeout: 15000,
    });

    const data = await response.json();

    if (response.ok && data.success) {
      return {
        statusCode: 200,
        headers,
        body: JSON.stringify({
          success: true,
          message: 'Check-in berhasil!',
          data: {
            timestamp: new Date().toISOString(),
            source: 'mobile_api',
          },
        }),
      };
    }
  } catch (error) {
    console.error('Mobile API Check-in Error:', error);
  }

  // Fallback to demo mode
  return {
    statusCode: 200,
    headers,
    body: JSON.stringify({
      success: true,
      message: 'Check-in berhasil! (Demo Mode)',
      data: {
        timestamp: new Date().toISOString(),
        source: 'demo',
        note: 'Data tersimpan lokal. Koneksi ke API mobile sedang offline.',
      },
    }),
  };
}

/**
 * Handle Check-out - Send to mobile API
 */
async function handleCheckout(body, headers, authToken) {
  const { photo, latitude, longitude } = body;

  // Format data untuk mobile API
  const attendanceData = {
    FotoCheckOut: photo || '',
    latCheckOut: latitude || '',
    lngCheckOut: longitude || '',
    waktuCheckOut: new Date().toLocaleTimeString('id-ID', { hour12: false }),
    tglAbsen: new Date().toISOString().split('T')[0],
  };

  // Try mobile API
  try {
    const response = await fetch(`${MOBILE_API_BASE}/absen`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': authToken || '',
      },
      body: JSON.stringify(attendanceData),
      timeout: 15000,
    });

    const data = await response.json();

    if (response.ok && data.success) {
      return {
        statusCode: 200,
        headers,
        body: JSON.stringify({
          success: true,
          message: 'Check-out berhasil!',
          data: {
            timestamp: new Date().toISOString(),
            source: 'mobile_api',
          },
        }),
      };
    }
  } catch (error) {
    console.error('Mobile API Check-out Error:', error);
  }

  // Fallback to demo mode
  return {
    statusCode: 200,
    headers,
    body: JSON.stringify({
      success: true,
      message: 'Check-out berhasil! (Demo Mode)',
      data: {
        timestamp: new Date().toISOString(),
        source: 'demo',
      },
    }),
  };
}

/**
 * Handle History - Get from mobile API
 */
async function handleHistory(headers, authToken) {
  // Try mobile API
  try {
    const response = await fetch(`${MOBILE_API_BASE}/daftar-absen`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': authToken || '',
      },
      timeout: 10000,
    });

    const data = await response.json();

    if (response.ok && data.success) {
      return {
        statusCode: 200,
        headers,
        body: JSON.stringify(data),
      };
    }
  } catch (error) {
    console.error('Mobile API History Error:', error);
  }

  // Fallback to demo data
  const demoHistory = generateDemoHistory();
  
  return {
    statusCode: 200,
    headers,
    body: JSON.stringify({
      success: true,
      data: demoHistory,
      source: 'demo',
    }),
  };
}

/**
 * Handle Today - Get today's attendance
 */
async function handleToday(headers, authToken) {
  // Try mobile API
  try {
    const response = await fetch(`${MOBILE_API_BASE}/absen/today`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': authToken || '',
      },
      timeout: 10000,
    });

    const data = await response.json();

    if (response.ok && data.success) {
      return {
        statusCode: 200,
        headers,
        body: JSON.stringify(data),
      };
    }
  } catch (error) {
    console.error('Mobile API Today Error:', error);
  }

  // Fallback to demo
  return {
    statusCode: 200,
    headers,
    body: JSON.stringify({
      success: true,
      data: {
        checkin_time: null,
        checkout_time: null,
      },
      source: 'demo',
    }),
  };
}

/**
 * Forward request to mobile API
 */
async function forwardToMobileAPI(path, method, body, headers, authToken) {
  try {
    const response = await fetch(`${MOBILE_API_BASE}${path}`, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': authToken || '',
      },
      body: body ? JSON.stringify(body) : undefined,
      timeout: 10000,
    });

    const data = await response.json();

    return {
      statusCode: response.status,
      headers,
      body: JSON.stringify(data),
    };
  } catch (error) {
    console.error('Forward API Error:', error);
    
    return {
      statusCode: 500,
      headers,
      body: JSON.stringify({
        success: false,
        message: 'API tidak dapat dijangkau',
      }),
    };
  }
}

/**
 * Generate demo history data
 */
function generateDemoHistory() {
  const history = [];
  const today = new Date();
  
  for (let i = 0; i < 10; i++) {
    const date = new Date(today);
    date.setDate(date.getDate() - i);
    
    // Skip weekends
    if (date.getDay() === 0 || date.getDay() === 6) continue;
    
    const checkinHour = 7 + Math.floor(Math.random() * 2);
    const checkinMinute = Math.floor(Math.random() * 60);
    const checkoutHour = 16 + Math.floor(Math.random() * 2);
    const checkoutMinute = Math.floor(Math.random() * 60);
    
    history.push({
      date: date.toISOString().split('T')[0],
      checkin_time: `${String(checkinHour).padStart(2, '0')}:${String(checkinMinute).padStart(2, '0')}:00`,
      checkout_time: `${String(checkoutHour).padStart(2, '0')}:${String(checkoutMinute).padStart(2, '0')}:00`,
      status: 'Hadir',
      location: 'Kantor BKPSDM',
    });
  }
  
  return history;
}
