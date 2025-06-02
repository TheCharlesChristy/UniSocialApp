// SocialConnect Camera Capture Component
// Handles camera access, photo/video capture, file uploads, and media preview

class CameraCapture {
    constructor() {        // DOM elements
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
        
        // Flipped video canvas for recording
        this.flippedCanvas = null;
        this.flippedContext = null;
        this.flippedStream = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.requestCameraAccess();
    }
      bindEvents() {
        // Mode selection
        this.photoModeBtn.addEventListener('click', () => {
            console.log('Photo mode button clicked');
            this.setMode('photo');
        });
        this.videoModeBtn.addEventListener('click', () => {
            console.log('Video mode button clicked');
            this.setMode('video');
        });
        
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
            
            // First try to get both video and audio
            const constraints = {
                video: {
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                    facingMode: 'user'
                },
                audio: true
            };
            
            try {
                this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                console.log('Camera and audio stream obtained:', this.stream);
                console.log('Video tracks:', this.stream.getVideoTracks().length);
                console.log('Audio tracks:', this.stream.getAudioTracks().length);
            } catch (error) {
                console.warn('Failed to get audio permission, trying video-only:', error);
                
                // Fallback to video-only if audio permission is denied
                const videoOnlyConstraints = {
                    video: {
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                        facingMode: 'user'
                    }
                };
                
                this.stream = await navigator.mediaDevices.getUserMedia(videoOnlyConstraints);
                console.log('Video-only stream obtained:', this.stream);
                console.log('Video tracks:', this.stream.getVideoTracks().length);
                console.log('Audio tracks:', this.stream.getAudioTracks().length);
            }
            
            this.setupCameraStream();
            this.hideCameraPermission();
            
        } catch (error) {
            console.error('Camera access error:', error);
            this.showCameraError(error);
        }
    }setupCameraStream() {
        if (this.stream && this.cameraStream) {
            this.cameraStream.srcObject = this.stream;
            this.captureBtn.disabled = false;
            
            // Create a flipped canvas for video recording
            this.setupFlippedCanvas();
            
            // Don't disable video mode immediately - wait for canvas to be ready
            // The MediaRecorder will be set up in setupFlippedCanvas after metadata loads
        }
    }
      setupFlippedCanvas() {
        // Create a hidden canvas for flipping video
        this.flippedCanvas = document.createElement('canvas');
        this.flippedContext = this.flippedCanvas.getContext('2d');
        this.flippedCanvas.style.display = 'none';
        document.body.appendChild(this.flippedCanvas);
        
        // Set canvas size once video dimensions are available
        this.cameraStream.addEventListener('loadedmetadata', () => {
            this.flippedCanvas.width = this.cameraStream.videoWidth;
            this.flippedCanvas.height = this.cameraStream.videoHeight;
            
            // Create stream from flipped canvas
            this.flippedStream = this.flippedCanvas.captureStream(30);
            
            // Now setup MediaRecorder with the flipped stream
            this.setupMediaRecorder();
            
            // Start the flipping animation loop
            this.startFlippingLoop();
        });
    }    setupMediaRecorder() {
        console.log('Setting up MediaRecorder...');
        try {
            // Create a combined stream with flipped video and original audio (if available)
            const combinedStream = new MediaStream();
            
            // Add the flipped video track from canvas
            const videoTracks = this.flippedStream.getVideoTracks();
            if (videoTracks.length > 0) {
                combinedStream.addTrack(videoTracks[0]);
                console.log('Added flipped video track');
            }
            
            // Add the original audio track from camera stream (if available)
            const audioTracks = this.stream.getAudioTracks();
            if (audioTracks.length > 0) {
                combinedStream.addTrack(audioTracks[0]);
                console.log('Added original audio track');
            } else {
                console.warn('No audio tracks found - video will be recorded without audio');
            }
              // Try different codec options for better browser compatibility
            let options;
            const hasAudio = audioTracks.length > 0;
            
            // Priority order: MP4 > WebM with VP8 > WebM with VP9 > basic WebM
            if (MediaRecorder.isTypeSupported('video/mp4')) {
                options = { mimeType: 'video/mp4' };
                console.log('Using MP4 format (most compatible)');
            } else if (hasAudio && MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')) {
                options = { mimeType: 'video/webm;codecs=vp8,opus' };
                console.log('Using VP8 with Opus audio');
            } else if (MediaRecorder.isTypeSupported('video/webm;codecs=vp8')) {
                options = { mimeType: 'video/webm;codecs=vp8' };
                console.log('Using VP8 codec (video only)');
            } else if (hasAudio && MediaRecorder.isTypeSupported('video/webm;codecs=vp9,opus')) {
                options = { mimeType: 'video/webm;codecs=vp9,opus' };
                console.log('Using VP9 with Opus audio');
            } else if (MediaRecorder.isTypeSupported('video/webm;codecs=vp9')) {
                options = { mimeType: 'video/webm;codecs=vp9' };
                console.log('Using VP9 codec (video only)');
            } else if (MediaRecorder.isTypeSupported('video/webm')) {
                options = { mimeType: 'video/webm' };
                console.log('Using basic WebM');
            } else {
                options = {};
                console.log('Using default options');
            }
            
            console.log('Creating MediaRecorder with combined stream');
            console.log('Stream tracks:', combinedStream.getTracks().map(t => `${t.kind}: ${t.label || 'unlabeled'}`));
            this.mediaRecorder = new MediaRecorder(combinedStream, options);
            
            this.mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    this.recordedChunks.push(event.data);
                }
            };
            
            this.mediaRecorder.onstop = () => {
                this.capturedMediaBlob = new Blob(this.recordedChunks, {
                    type: this.mediaRecorder.mimeType || 'video/webm'
                });
                this.capturedMediaType = 'video';
                this.recordedChunks = [];
                this.showPreview();
            };
            
            console.log('MediaRecorder initialized successfully with mimeType:', this.mediaRecorder.mimeType);
            console.log('Video button should be clickable now');
            
        } catch (error) {
            console.error('MediaRecorder setup failed:', error);
            this.videoModeBtn.disabled = true;
            this.videoModeBtn.title = 'Video recording not supported in this browser';
            console.log('Video button disabled due to MediaRecorder error');
        }
    }
    
    startFlippingLoop() {
        const flipFrame = () => {
            if (this.stream && this.flippedCanvas) {
                // Clear canvas
                this.flippedContext.clearRect(0, 0, this.flippedCanvas.width, this.flippedCanvas.height);
                
                // Apply horizontal flip
                this.flippedContext.scale(-1, 1);
                this.flippedContext.translate(-this.flippedCanvas.width, 0);
                
                // Draw the video frame
                this.flippedContext.drawImage(this.cameraStream, 0, 0);
                
                // Reset transformation for next frame
                this.flippedContext.setTransform(1, 0, 0, 1, 0, 0);
                
                // Continue animation if stream is active
                if (this.stream) {
                    requestAnimationFrame(flipFrame);
                }
            }
        };
        
        // Start the loop
        requestAnimationFrame(flipFrame);
    }    setMode(mode) {
        console.log('Setting mode to:', mode);
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
        
        // Apply horizontal flip transformation to canvas
        context.scale(-1, 1);
        context.translate(-canvas.width, 0);
        
        // Draw current frame (flipped)
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
            // Remove any CSS flip for preview since image is already flipped
            this.previewImage.style.transform = 'none';
        } else {
            this.previewVideo.src = url;
            this.previewVideo.style.display = 'block';
            this.previewImage.style.display = 'none';
            // Remove any CSS flip for preview since video is already flipped
            this.previewVideo.style.transform = 'none';
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
            // Generate filename
            const timestamp = Date.now();
            const extension = this.capturedMediaType === 'image' ? 'jpg' : 'webm';
            const filename = `capture_${timestamp}.${extension}`;
            
            const mediaData = {
                filename: filename,
                type: this.capturedMediaType,
                size: this.capturedMediaBlob.size,
                timestamp: timestamp
            };            // Check file size and type - use different storage methods based on content
            const maxSessionStorageSize = 2 * 1024 * 1024; // 2MB limit for sessionStorage
            const isVideo = this.capturedMediaType === 'video';
            
            // Always use IndexedDB for videos to prevent playback issues
            // Only use sessionStorage for small images
            if (isVideo) {
                // ALL videos use IndexedDB for proper blob handling and playback
                console.log('Using IndexedDB for video (size:', this.capturedMediaBlob.size, 'bytes, type:', this.capturedMediaBlob.type, ')');
                await this.storeInIndexedDB(this.capturedMediaBlob, mediaData);
                mediaData.storageMethod = 'indexedDB';
                sessionStorage.setItem('capturedMedia', JSON.stringify(mediaData));
                
                // Redirect to confirm post page
                window.location.href = '../pages/confirm_post.php';
            } else if (this.capturedMediaBlob.size <= maxSessionStorageSize) {
                // Small images only - use sessionStorage with base64
                console.log('Using sessionStorage for small image:', this.capturedMediaBlob.size, 'bytes');
                const reader = new FileReader();
                reader.onload = () => {
                    mediaData.dataUrl = reader.result;
                    mediaData.storageMethod = 'sessionStorage';
                    sessionStorage.setItem('capturedMedia', JSON.stringify(mediaData));
                    
                    // Redirect to confirm post page
                    window.location.href = '../pages/confirm_post.php';
                };
                reader.readAsDataURL(this.capturedMediaBlob);
            } else {
                // Large images - use IndexedDB for better blob handling
                console.log('Using IndexedDB for large image:', this.capturedMediaBlob.size, 'bytes');
                await this.storeInIndexedDB(this.capturedMediaBlob, mediaData);
                mediaData.storageMethod = 'indexedDB';
                sessionStorage.setItem('capturedMedia', JSON.stringify(mediaData));
                
                // Redirect to confirm post page
                window.location.href = '../pages/confirm_post.php';
            }
            
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
      async storeInIndexedDB(blob, mediaData) {
        return new Promise((resolve, reject) => {
            // Open IndexedDB
            const dbRequest = indexedDB.open('SocialConnectMedia', 1);
            
            dbRequest.onerror = () => {
                console.error('IndexedDB error:', dbRequest.error);
                reject(dbRequest.error);
            };
            
            dbRequest.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains('media')) {
                    db.createObjectStore('media', { keyPath: 'filename' });
                }
            };
            
            dbRequest.onsuccess = (event) => {
                const db = event.target.result;
                const transaction = db.transaction(['media'], 'readwrite');
                const store = transaction.objectStore('media');
                
                // Store the blob with metadata
                const mediaRecord = {
                    filename: mediaData.filename,
                    blob: blob,
                    type: mediaData.type,
                    size: mediaData.size,
                    timestamp: mediaData.timestamp
                };
                
                const putRequest = store.put(mediaRecord);
                
                putRequest.onsuccess = () => {
                    console.log('Media stored in IndexedDB successfully');
                    resolve();
                };
                
                putRequest.onerror = () => {
                    console.error('Error storing media in IndexedDB:', putRequest.error);
                    reject(putRequest.error);
                };
                
                transaction.oncomplete = () => {
                    db.close();
                };
            };
        });
    }

    cleanup() {
        // Stop camera stream
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        // Stop flipped stream
        if (this.flippedStream) {
            this.flippedStream.getTracks().forEach(track => track.stop());
            this.flippedStream = null;
        }
        
        // Remove flipped canvas
        if (this.flippedCanvas && this.flippedCanvas.parentNode) {
            this.flippedCanvas.parentNode.removeChild(this.flippedCanvas);
            this.flippedCanvas = null;
            this.flippedContext = null;
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
