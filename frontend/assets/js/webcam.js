/**
 * Webcam Functions
 */

let stream = null;
let capturedPhotoData = null;

// Start camera
async function startCamera() {
    try {
        const constraints = {
            video: {
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        
        const video = document.getElementById('camera-stream');
        if (video) {
            video.srcObject = stream;
        }
        
        return true;
    } catch (error) {
        console.error('Camera error:', error);
        let message = 'Error accessing camera';
        
        if (error.name === 'NotAllowedError') {
            message = 'Camera permission denied';
        } else if (error.name === 'NotFoundError') {
            message = 'No camera found';
        }
        
        throw new Error(message);
    }
}

// Stop camera
function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
}

// Capture photo
function capturePhoto() {
    const video = document.getElementById('camera-stream');
    const canvas = document.getElementById('photo-canvas');
    const capturedImg = document.getElementById('captured-photo');
    
    if (!video || !canvas) return;
    
    // Set canvas size to video size
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);
    
    // Get data URL
    capturedPhotoData = canvas.toDataURL('image/jpeg', 0.8);
    
    // Show captured photo
    if (capturedImg) {
        capturedImg.src = capturedPhotoData;
        capturedImg.style.display = 'block';
    }
    
    // Hide video
    video.style.display = 'none';
    
    // Stop camera
    stopCamera();
    
    // Update buttons
    document.getElementById('btn-capture').style.display = 'none';
    document.getElementById('btn-retake').style.display = 'inline-flex';
    document.getElementById('btn-next-confirm').disabled = false;
    
    showToast('Foto berhasil diambil');
}

// Retake photo
async function retakePhoto() {
    const video = document.getElementById('camera-stream');
    const capturedImg = document.getElementById('captured-photo');
    
    // Hide captured photo
    if (capturedImg) {
        capturedImg.style.display = 'none';
    }
    
    // Show video
    if (video) {
        video.style.display = 'block';
    }
    
    // Restart camera
    try {
        await startCamera();
        
        // Update buttons
        document.getElementById('btn-capture').style.display = 'inline-flex';
        document.getElementById('btn-retake').style.display = 'none';
        document.getElementById('btn-next-confirm').disabled = true;
        
        capturedPhotoData = null;
    } catch (error) {
        showToast(error.message);
    }
}

// Get captured photo
function getCapturedPhoto() {
    return capturedPhotoData;
}
