/**
 * Authentication
 */

let currentUser = null;

// Check session on load
async function checkSession() {
    // Skip PHP session check if using Netlify Functions
    if (CONFIG.USE_NETLIFY_FUNCTIONS && !CONFIG.DEMO_MODE) {
        // Check localStorage instead
        const storedUser = storage.get('user');
        if (storedUser) {
            currentUser = storedUser;
            console.log('Session restored from localStorage');
            return true;
        }
        return false;
    }
    
    try {
        const result = await apiRequest(CONFIG.API.SESSION);
        
        if (result.logged_in && result.user) {
            currentUser = result.user;
            return true;
        }
    } catch (error) {
        console.error('Session check failed:', error);
    }
    
    return false;
}

// Login
async function login(nip, password) {
    try {
        // Get reCAPTCHA token
        const recaptchaToken = typeof grecaptcha !== 'undefined' && grecaptcha.getResponse ? grecaptcha.getResponse() : null;
        
        // Check if reCAPTCHA is filled
        if (!recaptchaToken || recaptchaToken.length === 0) {
            throw new Error('Silakan centang verifikasi "Saya bukan robot"');
        }
        
        const result = await apiRequest(CONFIG.API.LOGIN, 'POST', { 
            nip, 
            password,
            recaptchaToken 
        });
        
        if (result.success) {
            currentUser = result.data;
            storage.set('user', currentUser);
            
            // Store token if available
            if (result.token) {
                storage.set('auth_token', result.token);
            }
            
            // Log source for debugging
            if (result.source) {
                console.log('✓ Login successful via:', result.source);
                storage.set('api_source', result.source);
            }
            
            // Reset reCAPTCHA for next login
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset();
            }
            
            return true;
        }
    } catch (error) {
        // Reset reCAPTCHA on error
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.reset();
        }
        throw error;
    }
    
    return false;
}

// Logout
async function logout() {
    try {
        await apiRequest(CONFIG.API.LOGOUT, 'POST');
    } catch (error) {
        console.error('Logout error:', error);
    }
    
    currentUser = null;
    storage.clear();
    showPage('login-page');
    showToast('Logout berhasil');
}

// Get current user
function getCurrentUser() {
    return currentUser || storage.get('user');
}

// Populate user info
function populateUserInfo() {
    const user = getCurrentUser();
    if (!user) return;
    
    // Header
    document.getElementById('user-name').textContent = user.nama;
    document.getElementById('user-nip').textContent = user.nip;
    
    // Profile page
    const profileName = document.getElementById('profile-name');
    const profileNip = document.getElementById('profile-nip');
    const profilePosition = document.getElementById('profile-position');
    const profileUnit = document.getElementById('profile-unit');
    const profileEmail = document.getElementById('profile-email');
    const profilePhone = document.getElementById('profile-phone');
    
    if (profileName) profileName.textContent = user.nama;
    if (profileNip) profileNip.textContent = user.nip;
    if (profilePosition) profilePosition.textContent = user.jabatan || '-';
    if (profileUnit) profileUnit.textContent = user.unit_kerja || '-';
    if (profileEmail) profileEmail.textContent = user.email || '-';
    if (profilePhone) profilePhone.textContent = user.phone || '-';
}
