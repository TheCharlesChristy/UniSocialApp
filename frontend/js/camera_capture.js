// SocialConnect Camera Capture Component
// Handles camera access, photo/video capture, file uploads, and media preview

class CameraCapture {
    constructor() {
        // DOM elements
        this.cameraView = document.getElementById('cameraView');
        this.previewView = document.getElementById('previewView');
        this.cameraStream = document.getElementById('cameraStream');
        this.cameraPermission = document.getElementById('cameraPermission');
        this.cameraError = document.getElementById('cameraError');
        this.errorText = document.getElementById('errorText');
        
        // Mode controls
        this.photoModeBtn = document.getElementById('photoModeBtn');
        this.videoModeBtn = document.getElementById('videoModeBtn');
        this.captureBtn = document.getElementById('captureBtn');
        this.captureBtnInner = document.getElementById('captureBtnInner');
        
        // File upload
        this.fileInput = document.getElementById('fileInput');
        
        // Preview elements
        this.previewImage = document.getElementById('previewImage');
        this.previewVideo = document.getElementById('previewVideo');
        this.goBackBtn = document.getElementById('goBackBtn');
        this.confirmBtn = document.getElementById('confirmBtn');
        
        // Recording elements
        this.recordingIndicator = document.getElementById('recordingIndicator');
        this.recordingTime = document.getElementById('recordingTime');
        this.requestPermissionBtn = document.getElementById('requestPermissionBtn');
        
        // State
        this.currentMode = 'photo';
        this.stream = null;
        this.mediaRecorder = null;
        this.recordedChunks = [];
        this.isRecording = false;
        this.recordingStartTime = 0;
        this.recordingInterval = null;
        this.capturedMediaBlob = null;
        this.capturedMediaType = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.requestCameraAccess();
    }
    
    bindEvents() {
        // Mode selection
        this.photoModeBtn.addEventListener('click', () => this.setMode('photo'));
        this.videoModeBtn.addEventListener('click', () => this.setMode('video'));
        
        // Capture button
        this.captureBtn.addEventListener('click', () => this.handleCapture());
        
        // File upload
        this.fileInput.addEventListener('change', (e) => this.handleFileUpload(e));
        
        // Permission request
        this.requestPermissionBtn.addEventListener('click', () => this.requestCameraAccess());
        
        // Preview controls
        this.goBackBtn.addEventListener('click', () => this.goBack());
        this.confirmBtn.addEventListener('click', () => this.confirmMedia());
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => this.handleKeyDown(e));
    }
    
    handleKeyDown(e) {
        if (e.key === 'Escape') {
            if (this.previewView.style.display !== 'none') {
                this.goBack();
            }
        } else if (e.key === ' ' || e.key === 'Enter') {
            if (this.cameraView.style.display !== 'none' && !this.captureBtn.disabled) {
                e.preventDefault();
                this.handleCapture();
            }
        }
    }
    
    async requestCameraAccess() {
        try {
            this.showCameraPermission();
            
            const constraints = {
                video: {
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                    facingMode: 'user'
                },
                audio: true
            };
            
            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.setupCameraStream();
            this.hideCameraPermission();
            
        } catch (error) {
            console.error('Camera access error:', error);
            this.showCameraError(error);
        }
    }
    
    setupCameraStream() {
        if (this.stream && this.cameraStream) {
            this.cameraStream.srcObject = this.stream;
            this.captureBtn.disabled = false;
            
            // Setup media recorder for video mode
            try {
                this.mediaRecorder = new MediaRecorder(this.stream, {
                    mimeType: 'video/webm;codecs=vp9'
                });
                
                this.mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        this.recordedChunks.push(event.data);
                    }
                };
                
                this.mediaRecorder.onstop = () => {
                    this.capturedMediaBlob = new Blob(this.recordedChunks, {
                        type: 'video/webm'
                    });
                    this.capturedMediaType = 'video';
                    this.recordedChunks = [];
                    this.showPreview();
                };
                
            } catch (error) {
                console.warn('MediaRecorder not supported, video recording disabled:', error);
                this.videoModeBtn.disabled = true;
            }
        }
    }
    
    setMode(mode) {
        this.currentMode = mode;
        
        // Update mode buttons
        this.photoModeBtn.classList.toggle('active', mode === 'photo');
        this.videoModeBtn.classList.toggle('active', mode === 'video');
        
        // Update capture button icon
        const photoIcon = this.captureBtnInner.querySelector('.photo-icon');
        const videoIcon = this.captureBtnInner.querySelector('.video-icon');
        
        if (mode === 'photo') {
            photoIcon.style.display = 'block';
            videoIcon.style.display = 'none';
        } else {
            photoIcon.style.display = 'none';
            videoIcon.style.display = 'block';
        }
    }
    
    handleCapture() {
        if (this.currentMode === 'photo') {
            this.capturePhoto();
        } else if (this.currentMode === 'video') {
            if (this.isRecording) {
                this.stopVideoRecording();
            } else {
                this.startVideoRecording();
            }
        }
    }
    
    capturePhoto() {
        if (!this.stream) return;
        
        // Create canvas to capture frame
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        
        canvas.width = this.cameraStream.videoWidth;
        canvas.height = this.cameraStream.videoHeight;
        
        // Draw current frame
        context.drawImage(this.cameraStream, 0, 0);
        
        // Convert to blob
        canvas.toBlob((blob) => {
            this.capturedMediaBlob = blob;
            this.capturedMediaType = 'image';
            this.showPreview();
        }, 'image/jpeg', 0.9);
    }
    
    startVideoRecording() {
        if (!this.mediaRecorder || this.mediaRecorder.state !== 'inactive') return;
        
        this.recordedChunks = [];
        this.mediaRecorder.start();
        this.isRecording = true;
        this.recordingStartTime = Date.now();
        
        // Update UI
        this.captureBtn.classList.add('recording');
        this.recordingIndicator.style.display = 'flex';
        
        // Update icons
        const videoIcon = this.captureBtnInner.querySelector('.video-icon');
        const stopIcon = this.captureBtnInner.querySelector('.stop-icon');
        videoIcon.style.display = 'none';
        stopIcon.style.display = 'block';
        
        // Start timer
        this.recordingInterval = setInterval(() => {
            this.updateRecordingTime();
        }, 1000);
        
        // Disable mode switching during recording
        this.photoModeBtn.disabled = true;
        this.videoModeBtn.disabled = true;
    }
    
    stopVideoRecording() {
        if (!this.mediaRecorder || this.mediaRecorder.state !== 'recording') return;
        
        this.mediaRecorder.stop();
        this.isRecording = false;
        
        // Update UI
        this.captureBtn.classList.remove('recording');
        this.recordingIndicator.style.display = 'none';
        
        // Reset icons
        const videoIcon = this.captureBtnInner.querySelector('.video-icon');
        const stopIcon = this.captureBtnInner.querySelector('.stop-icon');
        videoIcon.style.display = 'block';
        stopIcon.style.display = 'none';
        
        // Clear timer
        if (this.recordingInterval) {
            clearInterval(this.recordingInterval);
            this.recordingInterval = null;
        }
        
        // Re-enable mode switching
        this.photoModeBtn.disabled = false;
        this.videoModeBtn.disabled = false;
    }
    
    updateRecordingTime() {
        const elapsed = Math.floor((Date.now() - this.recordingStartTime) / 1000);
        const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
        const seconds = (elapsed % 60).toString().padStart(2, '0');
        this.recordingTime.textContent = `${minutes}:${seconds}`;
    }
    
    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validate file type
        if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) {
            alert('Please select an image or video file.');
            return;
        }
        
        // Check file size (50MB limit)
        const maxSize = 50 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size must be less than 50MB.');
            return;
        }
        
        this.capturedMediaBlob = file;
        this.capturedMediaType = file.type.startsWith('image/') ? 'image' : 'video';
        this.showPreview();
        
        // Reset file input
        event.target.value = '';
    }
    
    showPreview() {
        if (!this.capturedMediaBlob) return;
        
        const url = URL.createObjectURL(this.capturedMediaBlob);
        
        if (this.capturedMediaType === 'image') {
            this.previewImage.src = url;
            this.previewImage.style.display = 'block';
            this.previewVideo.style.display = 'none';
        } else {
            this.previewVideo.src = url;
            this.previewVideo.style.display = 'block';
            this.previewImage.style.display = 'none';
        }
        
        // Switch views
        this.cameraView.style.display = 'none';
        this.previewView.style.display = 'block';
    }
    
    goBack() {
        // Clean up preview URLs
        if (this.previewImage.src) {
            URL.revokeObjectURL(this.previewImage.src);
            this.previewImage.src = '';
        }
        if (this.previewVideo.src) {
            URL.revokeObjectURL(this.previewVideo.src);
            this.previewVideo.src = '';
        }
        
        // Hide preview elements
        this.previewImage.style.display = 'none';
        this.previewVideo.style.display = 'none';
        
        // Reset captured media
        this.capturedMediaBlob = null;
        this.capturedMediaType = null;
        
        // Switch views
        this.previewView.style.display = 'none';
        this.cameraView.style.display = 'block';
    }
    
    async confirmMedia() {
        if (!this.capturedMediaBlob) return;
        
        try {
            // Create FormData to send the file
            const formData = new FormData();
            
            // Generate filename
            const timestamp = Date.now();
            const extension = this.capturedMediaType === 'image' ? 'jpg' : 'webm';
            const filename = `capture_${timestamp}.${extension}`;
            
            formData.append('media', this.capturedMediaBlob, filename);
            formData.append('type', this.capturedMediaType);
            
            // Store in session or temporary storage
            // For now, we'll use localStorage to pass data to the next page
            const mediaData = {
                filename: filename,
                type: this.capturedMediaType,
                size: this.capturedMediaBlob.size,
                timestamp: timestamp
            };
            
            // Convert blob to base64 for storage (for demo purposes)
            // In production, you'd upload to server and get a URL
            const reader = new FileReader();
            reader.onload = () => {
                mediaData.dataUrl = reader.result;
                sessionStorage.setItem('capturedMedia', JSON.stringify(mediaData));
                
                // Redirect to confirm post page
                window.location.href = '../pages/confirm_post.php';
            };
            reader.readAsDataURL(this.capturedMediaBlob);
            
        } catch (error) {
            console.error('Error confirming media:', error);
            alert('Error processing media. Please try again.');
        }
    }
    
    showCameraPermission() {
        this.cameraStream.style.display = 'none';
        this.cameraPermission.style.display = 'flex';
        this.cameraError.style.display = 'none';
    }
    
    hideCameraPermission() {
        this.cameraStream.style.display = 'block';
        this.cameraPermission.style.display = 'none';
        this.cameraError.style.display = 'none';
    }
    
    showCameraError(error) {
        let errorMessage = 'Unable to access camera. You can still upload media from your device.';
        
        if (error.name === 'NotAllowedError') {
            errorMessage = 'Camera access was denied. Please allow camera access or upload media from your device.';
        } else if (error.name === 'NotFoundError') {
            errorMessage = 'No camera found on this device. You can upload media from your device instead.';
        } else if (error.name === 'NotSupportedError') {
            errorMessage = 'Camera not supported in this browser. You can upload media from your device.';
        }
        
        this.errorText.textContent = errorMessage;
        this.cameraStream.style.display = 'none';
        this.cameraPermission.style.display = 'none';
        this.cameraError.style.display = 'flex';
        this.captureBtn.disabled = true;
    }
    
    cleanup() {
        // Stop camera stream
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
        
        // Clear recording interval
        if (this.recordingInterval) {
            clearInterval(this.recordingInterval);
        }
        
        // Clean up URLs
        if (this.previewImage.src) {
            URL.revokeObjectURL(this.previewImage.src);
        }
        if (this.previewVideo.src) {
            URL.revokeObjectURL(this.previewVideo.src);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new CameraCapture();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    const cameraCapture = window.cameraCapture;
    if (cameraCapture) {
        cameraCapture.cleanup();
    }
});
