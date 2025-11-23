/**
 * Attendance Functions
 */

let attendanceType = 'checkin'; // 'checkin' or 'checkout'
let attendanceData = {};

// Show attendance page
async function showAttendancePage(type) {
    attendanceType = type;
    attendanceData = {};
    
    document.getElementById('attendance-title').textContent = 
        type === 'checkin' ? 'Check In' : 'Check Out';
    
    showPage('attendance-page');
    
    // Reset steps
    resetSteps();
    
    // Start location acquisition
    await getLocationStep();
}

// Reset steps
function resetSteps() {
    document.querySelectorAll('.step').forEach(step => step.classList.remove('active', 'completed'));
    document.querySelectorAll('.step-content').forEach(content => content.classList.remove('active'));
    
    document.getElementById('step-1').classList.add('active');
    document.getElementById('step-location').classList.add('active');
}

// Step 1: Get Location
async function getLocationStep() {
    const statusDiv = document.getElementById('location-status');
    const resultDiv = document.getElementById('location-result');
    const nextBtn = document.getElementById('btn-next-photo');
    
    statusDiv.style.display = 'block';
    resultDiv.style.display = 'none';
    nextBtn.disabled = true;
    
    try {
        const location = await getCurrentLocation();
        
        attendanceData.latitude = location.latitude;
        attendanceData.longitude = location.longitude;
        attendanceData.accuracy = location.accuracy;
        
        // Check geofence
        const geofenceResult = checkGeofence(location.latitude, location.longitude);
        attendanceData.geofence = geofenceResult;
        
        // Display results
        document.getElementById('location-lat').textContent = location.latitude.toFixed(6);
        document.getElementById('location-lng').textContent = location.longitude.toFixed(6);
        document.getElementById('location-accuracy').textContent = Math.round(location.accuracy) + ' m';
        
        const statusBadge = document.getElementById('location-status-badge');
        if (geofenceResult.valid || !CONFIG.SECURITY.REQUIRE_GEOFENCING) {
            statusBadge.textContent = '✓ ' + geofenceResult.message;
            statusBadge.className = 'value status-badge success';
            nextBtn.disabled = false;
        } else {
            statusBadge.textContent = '✗ ' + geofenceResult.message;
            statusBadge.className = 'value status-badge danger';
            
            if (!CONFIG.DEMO_MODE) {
                showToast('Anda berada di luar area kantor', 5000);
                return;
            } else {
                // Allow in demo mode
                nextBtn.disabled = false;
            }
        }
        
        statusDiv.style.display = 'none';
        resultDiv.style.display = 'block';
        
    } catch (error) {
        statusDiv.innerHTML = `
            <i class="fas fa-exclamation-circle" style="color: var(--danger-color); font-size: 48px;"></i>
            <p style="color: var(--danger-color);">${error.message}</p>
            <button class="btn btn-primary" onclick="getLocationStep()">Coba Lagi</button>
        `;
    }
}

// Step 2: Photo
async function nextToPhoto() {
    // Mark step 1 complete
    document.getElementById('step-1').classList.remove('active');
    document.getElementById('step-1').classList.add('completed');
    document.getElementById('step-location').classList.remove('active');
    
    // Activate step 2
    document.getElementById('step-2').classList.add('active');
    document.getElementById('step-photo').classList.add('active');
    
    // Check if face verification required
    if (!CONFIG.SECURITY.REQUIRE_FACE_VERIFICATION && CONFIG.DEMO_MODE) {
        // Show option to skip
        const skipBtn = document.createElement('button');
        skipBtn.className = 'btn btn-secondary btn-block mt-2';
        skipBtn.textContent = 'Skip (Demo Mode)';
        skipBtn.onclick = () => {
            capturedPhotoData = 'data:image/png;base64,demo_photo';
            nextToConfirm();
        };
        
        const photoStep = document.getElementById('step-photo');
        if (!photoStep.querySelector('.btn-secondary')) {
            photoStep.appendChild(skipBtn);
        }
    }
    
    // Start camera
    try {
        await startCamera();
    } catch (error) {
        showToast(error.message);
    }
}

// Step 3: Confirmation
function nextToConfirm() {
    const photo = getCapturedPhoto();
    if (!photo) {
        showToast('Ambil foto terlebih dahulu');
        return;
    }
    
    attendanceData.photo = photo;
    
    // Mark step 2 complete
    document.getElementById('step-2').classList.remove('active');
    document.getElementById('step-2').classList.add('completed');
    document.getElementById('step-photo').classList.remove('active');
    
    // Activate step 3
    document.getElementById('step-3').classList.add('active');
    document.getElementById('step-confirm').classList.add('active');
    
    // Populate confirmation data
    document.getElementById('confirm-location').textContent = 
        `${attendanceData.latitude.toFixed(6)}, ${attendanceData.longitude.toFixed(6)}`;
    document.getElementById('confirm-area').textContent = 
        attendanceData.geofence.office || attendanceData.geofence.message;
    document.getElementById('confirm-photo').src = photo;
    document.getElementById('confirm-date').textContent = formatDate(new Date());
    document.getElementById('confirm-time').textContent = formatTime(new Date());
}

// Submit attendance
async function submitAttendance() {
    const submitBtn = document.getElementById('btn-submit');
    setButtonLoading(submitBtn, true);
    
    try {
        const endpoint = attendanceType === 'checkin' ? CONFIG.API.CHECKIN : CONFIG.API.CHECKOUT;
        
        const result = await apiRequest(endpoint, 'POST', attendanceData);
        
        if (result.success) {
            showToast(result.message);
            
            // Reset and go back to dashboard
            setTimeout(() => {
                backToDashboard();
                loadTodayAttendance();
            }, 1500);
        }
    } catch (error) {
        showToast(error.message || 'Terjadi kesalahan');
    } finally {
        setButtonLoading(submitBtn, false);
    }
}

// Load today's attendance
async function loadTodayAttendance() {
    try {
        const result = await apiRequest(CONFIG.API.TODAY);
        
        if (result.success && result.data) {
            const data = result.data;
            
            document.getElementById('checkin-time').textContent = 
                data.checkin_time ? data.checkin_time : 'Belum Absen';
            document.getElementById('checkout-time').textContent = 
                data.checkout_time ? data.checkout_time : 'Belum Absen';
            
            // Update button states
            const checkinBtn = document.getElementById('btn-checkin');
            const checkoutBtn = document.getElementById('btn-checkout');
            
            if (data.checkin_time) {
                checkinBtn.disabled = true;
                checkoutBtn.disabled = false;
            }
            
            if (data.checkout_time) {
                checkoutBtn.disabled = true;
            }
        } else {
            // No attendance today
            document.getElementById('checkin-time').textContent = 'Belum Absen';
            document.getElementById('checkout-time').textContent = 'Belum Absen';
            document.getElementById('btn-checkin').disabled = false;
            document.getElementById('btn-checkout').disabled = true;
        }
    } catch (error) {
        console.error('Load attendance error:', error);
    }
}

// Load attendance history
async function loadHistory() {
    try {
        const result = await apiRequest(CONFIG.API.HISTORY);
        
        if (result.success && result.data) {
            const historyList = document.getElementById('history-list');
            
            if (result.data.length === 0) {
                historyList.innerHTML = '<p class="text-center">Belum ada riwayat</p>';
                return;
            }
            
            historyList.innerHTML = result.data.map(item => `
                <div class="history-item">
                    <div class="history-date">${formatDate(item.date)}</div>
                    <div class="history-times">
                        <div>
                            <i class="fas fa-sign-in-alt"></i>
                            ${item.checkin_time}
                        </div>
                        <div>
                            <i class="fas fa-sign-out-alt"></i>
                            ${item.checkout_time}
                        </div>
                    </div>
                    <div class="history-status ${item.status}">
                        ${item.status === 'on_time' ? '✓ Tepat Waktu' : '⚠ Terlambat'}
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Load history error:', error);
    }
}

// Back to dashboard
function backToDashboard() {
    // Stop camera if running
    stopCamera();
    
    showPage('dashboard-page');
    loadTodayAttendance();
}

// Show history page
function showHistoryPage() {
    showPage('history-page');
    loadHistory();
}

// Show profile page
function showProfilePage() {
    showPage('profile-page');
    populateUserInfo();
}

// Show settings page
function showSettingsPage() {
    showModal('Pengaturan', '<p>Fitur pengaturan akan segera tersedia</p>');
}

// Show help page
function showHelpPage() {
    showModal('Bantuan', `
        <h4>Cara Menggunakan:</h4>
        <ol>
            <li>Login dengan NIP dan password</li>
            <li>Klik Check In untuk absen masuk</li>
            <li>Izinkan akses lokasi dan kamera</li>
            <li>Ikuti langkah-langkah absensi</li>
            <li>Klik Check Out saat pulang</li>
        </ol>
        <p><strong>Demo Mode:</strong></p>
        <p>NIP: demo<br>Password: demo123</p>
    `);
}

// Show forgot password
function showForgotPassword() {
    showModal('Lupa Password', `
        <p>Untuk reset password, silakan hubungi admin atau bagian kepegawaian.</p>
        <p><strong>Kontak:</strong></p>
        <p>Email: kepegawaian@bekasikab.go.id<br>
        Telepon: (021) xxx-xxxx</p>
    `);
}

// Filter history
function filterHistory() {
    // Placeholder for filter functionality
    loadHistory();
}
