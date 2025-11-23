/**
 * Main Application
 */

// Initialize app
document.addEventListener('DOMContentLoaded', async () => {
    console.log('BKPSDMApp Backup - Initializing...');
    
    // Hide loading screen after a moment
    setTimeout(() => {
        document.getElementById('loading-screen').style.display = 'none';
        document.getElementById('app-container').style.display = 'block';
    }, 1000);
    
    // Check session
    const isLoggedIn = await checkSession();
    
    if (isLoggedIn) {
        // User is logged in, show dashboard
        showDashboard();
    } else {
        // Show login page
        showPage('login-page');
        
        // Auto-fill demo credentials if in demo mode
        if (CONFIG.DEMO_MODE) {
            setTimeout(() => {
                const nipInput = document.getElementById('nip');
                const passwordInput = document.getElementById('password');
                if (nipInput) nipInput.placeholder = 'Demo: demo';
                if (passwordInput) passwordInput.placeholder = 'Demo: demo123';
            }, 500);
        }
    }
    
    // Setup event listeners
    setupEventListeners();
    
    // Start clock update
    setInterval(updateClock, 1000);
    updateClock();
});

// Setup event listeners
function setupEventListeners() {
    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Close modal on outside click
    const modal = document.getElementById('modal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
}

// Handle login
async function handleLogin(e) {
    e.preventDefault();
    
    const nip = document.getElementById('nip').value.trim();
    const password = document.getElementById('password').value;
    
    if (!nip || !password) {
        showToast('NIP dan password harus diisi');
        return;
    }
    
    const loginForm = document.getElementById('login-form');
    const submitBtn = loginForm.querySelector('button[type="submit"]');
    setButtonLoading(submitBtn, true);
    
    try {
        const success = await login(nip, password);
        
        if (success) {
            showToast('Login berhasil!');
            
            // Show dashboard after brief delay
            setTimeout(() => {
                showDashboard();
            }, 500);
        }
    } catch (error) {
        showToast(error.message || 'Login gagal');
    } finally {
        setButtonLoading(submitBtn, false);
    }
}

// Show dashboard
function showDashboard() {
    showPage('dashboard-page');
    populateUserInfo();
    loadTodayAttendance();
    updateClock();
}

// Log for debugging
console.log('%cBKPSDMApp Backup System v1.0.0', 'color: blue; font-weight: bold;');
console.log('Demo Mode:', CONFIG.DEMO_MODE);
console.log('Use Netlify Functions:', CONFIG.USE_NETLIFY_FUNCTIONS);
console.log('API Base:', CONFIG.API_BASE_URL);

if (CONFIG.DEMO_MODE) {
    console.log('%cDEMO MODE ACTIVE', 'color: orange; font-size: 16px; font-weight: bold;');
    console.log('%cLogin with: NIP=demo, Password=demo123', 'color: green; font-size: 14px;');
} else if (CONFIG.USE_NETLIFY_FUNCTIONS) {
    console.log('%cPRODUCTION MODE - Using Netlify Functions', 'color: green; font-size: 14px; font-weight: bold;');
    console.log('Will connect to mobile backend API');
}
