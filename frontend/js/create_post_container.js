/**
 * Create Post Container Component JavaScript
 * Manages the complete post creation flow including media, location, privacy, and caption
 */

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializeCreatePostContainer();
    });
} else {
    initializeCreatePostContainer();
}

// Main component class
class CreatePostContainer {    constructor() {
        // DOM elements
        this.container = document.querySelector('.create-post-container');
        this.privacySelect = document.getElementById('privacyLevel');
        this.captionTextarea = document.getElementById('postCaption');
        this.characterCount = document.getElementById('characterCount');
        this.createPostButton = document.getElementById('createPostButton');
        this.loadingState = document.getElementById('postLoadingState');
        this.statusMessages = document.getElementById('postStatusMessages');
        
        // Component instances
        this.confirmMediaInstance = null;
        this.selectLocationInstance = null;
        
        // State
        this.mediaData = null;
        this.locationData = null;
        this.isSubmitting = false;
          // API handler
        this.apiHandler = new APIHandler();
        
        // Debug: Check if authenticatedUpload method exists
        console.log('APIHandler methods:', Object.getOwnPropertyNames(Object.getPrototypeOf(this.apiHandler)));
        console.log('authenticatedUpload exists:', typeof this.apiHandler.authenticatedUpload === 'function');
        
        this.init();
    }    init() {
        // Check if user is authenticated
        if (!this.apiHandler.isAuthenticated()) {
            console.warn('User not authenticated - some features may not work');
            this.showStatusMessage('Please log in to create posts', 'warning');
        }
        
        this.initializeSubComponents();
        this.setupEventListeners();
        this.setupCharacterCounter();
        this.loadInitialData();
    }
      // Initialize already-rendered sub-components
    initializeSubComponents() {
        try {
            // Initialize confirm_media component if it exists
            if (window.ConfirmMediaDisplay) {
                this.confirmMediaInstance = new ConfirmMediaDisplay();
                this.setupMediaEventListeners();
            } else {
                // Wait a bit and try again
                setTimeout(() => {
                    if (window.ConfirmMediaDisplay) {
                        this.confirmMediaInstance = new ConfirmMediaDisplay();
                        this.setupMediaEventListeners();
                    }
                }, 500);
            }
            
            // Initialize select_location component
            this.setupLocationEventListeners();
            
        } catch (error) {
            console.error('Error initializing sub-components:', error);
        }
    }    
    setupEventListeners() {
        // Caption textarea events
        this.captionTextarea.addEventListener('input', () => {
            this.updateCharacterCount();
            this.validateForm();
        });
        
        // Privacy select events
        this.privacySelect.addEventListener('change', () => {
            this.validateForm();
        });
        
        // Post button click
        this.createPostButton.addEventListener('click', () => {
            this.handleCreatePost();
        });
        
        // Form validation on input
        this.captionTextarea.addEventListener('blur', () => {
            this.validateForm();
        });
    }
    
    setupMediaEventListeners() {
        // Listen for media data changes
        if (this.confirmMediaInstance) {
            // Set up periodic checking for media data
            this.mediaCheckInterval = setInterval(() => {
                this.checkMediaData();
            }, 1000);
        }
    }
    
    setupLocationEventListeners() {
        // Listen for location data changes
        this.locationCheckInterval = setInterval(() => {
            this.checkLocationData();
        }, 1000);
    }
    
    checkMediaData() {
        try {
            const mediaDataString = sessionStorage.getItem('capturedMedia');
            if (mediaDataString) {
                const newMediaData = JSON.parse(mediaDataString);
                if (JSON.stringify(newMediaData) !== JSON.stringify(this.mediaData)) {
                    this.mediaData = newMediaData;
                    this.validateForm();
                    console.log('Media data updated:', this.mediaData);
                }
            } else if (this.mediaData) {
                this.mediaData = null;
                this.validateForm();
                console.log('Media data cleared');
            }
        } catch (error) {
            console.error('Error checking media data:', error);
        }
    }
    
    checkLocationData() {
        try {
            const locationDataString = sessionStorage.getItem('selectedLocation');
            if (locationDataString) {
                const newLocationData = JSON.parse(locationDataString);
                if (JSON.stringify(newLocationData) !== JSON.stringify(this.locationData)) {
                    this.locationData = newLocationData;
                    this.validateForm();
                    console.log('Location data updated:', this.locationData);
                }
            } else if (this.locationData) {
                this.locationData = null;
                this.validateForm();
                console.log('Location data cleared');
            }
        } catch (error) {
            console.error('Error checking location data:', error);
        }
    }
    
    setupCharacterCounter() {
        this.updateCharacterCount();
    }
    
    updateCharacterCount() {
        const length = this.captionTextarea.value.length;
        const maxLength = 2000;
        this.characterCount.textContent = `${length}/${maxLength}`;
        
        // Update styling based on character count
        this.characterCount.classList.remove('warning', 'error');
        if (length > maxLength * 0.9) {
            this.characterCount.classList.add('warning');
        }
        if (length > maxLength) {
            this.characterCount.classList.add('error');
        }
    }    validateForm() {
        const caption = this.captionTextarea.value.trim();
        const privacy = this.privacySelect.value;
        // Check for media data regardless of storage method
        const hasMedia = this.mediaData && (this.mediaData.dataUrl || this.mediaData.storageMethod === 'indexedDB');
        const isAuthenticated = this.apiHandler.isAuthenticated();
        
        // For text posts, caption is required
        // For media posts, caption is optional
        let isValid = false;
        
        if (hasMedia) {
            // Media post - only need privacy level and authentication
            isValid = privacy !== '' && isAuthenticated;
        } else {
            // Text post - need caption, privacy level, and authentication
            isValid = caption.length > 0 && caption.length <= 2000 && privacy !== '' && isAuthenticated;
        }
        
        this.createPostButton.disabled = !isValid || this.isSubmitting;
        
        // Update button text to indicate authentication requirement
        if (!isAuthenticated) {
            this.createPostButton.querySelector('.btn-text').textContent = 'Please Log In';
        } else {
            this.createPostButton.querySelector('.btn-text').textContent = 'Share Post';
        }
        
        return isValid;
    }
      async handleCreatePost() {
        if (!this.validateForm() || this.isSubmitting) {
            return;
        }
        
        // Double-check authentication before proceeding
        if (!this.apiHandler.isAuthenticated()) {
            this.showStatusMessage('Please log in to create posts', 'error');
            return;
        }
        
        this.isSubmitting = true;
        this.showLoadingState(true);
        this.clearStatusMessages();
          try {
            let postType = 'text';
            
            // Determine post type
            console.log("this.mediaData:", this.mediaData);
            if (this.mediaData && this.mediaData.type) {
                postType = this.mediaData.type === 'video' ? 'video' : 'photo';
            }
            
            // Create the post (with media file if present)
            console.log('Creating post...');
            const postData = await this.createPost(postType);
              // Success
            this.showStatusMessage('Post created successfully! Redirecting to feed...', 'success');
            this.resetForm();
            
            // Redirect to feed after a short delay to show success message
            setTimeout(() => {
                window.location.href = '../pages/feed.php';
            }, 1500);
            
        } catch (error) {
            console.error('Error creating post:', error);
            this.showStatusMessage(error.message || 'Failed to create post', 'error');
        } finally {
            this.isSubmitting = false;
            this.showLoadingState(false);
            this.validateForm();
        }    }
    
    // Custom authenticated upload method for FormData
    async authenticatedUpload(endpoint, formData) {
        const token = this.apiHandler.getAuthToken();
        if (!token) {
            throw new Error('No authentication token found');
        }
        
        const url = `${this.apiHandler.baseURL}${endpoint}.php`;
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                    // Deliberately not setting Content-Type to let browser set boundary for FormData
                },
                body: formData
            });
            
            const contentType = response.headers.get('content-type');
            let responseData;
            
            if (contentType && contentType.includes('application/json')) {
                responseData = await response.json();
            } else {
                responseData = await response.text();
            }
            
            console.log('Upload API Response:', {
                url,
                status: response.status,
                contentType,
                responseData
            });
            
            if (!response.ok) {
                if (typeof responseData === 'object' && responseData.message) {
                    throw new Error(responseData.message);
                } else if (typeof responseData === 'string' && responseData.trim()) {
                    throw new Error(responseData);
                } else {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
            }
            
            return responseData;
            
        } catch (error) {
            console.error('Authenticated upload failed:', {
                url,
                error: error.message,
                stack: error.stack
            });
            throw error;
        }
    }
      async uploadMedia() {
        if (!this.mediaData || !this.mediaData.dataUrl) {
            throw new Error('No media data to upload');
        }
        
        try {
            // Convert data URL to blob
            const response = await fetch(this.mediaData.dataUrl);
            const blob = await response.blob();
              // Create form data
            const formData = new FormData();
            const filename = `media_${Date.now()}.${this.mediaData.type === 'video' ? 'mp4' : 'jpg'}`;
            formData.append('file', blob, filename);
            formData.append('type', 'post_media');// Upload the media using custom authenticated upload
            const uploadResult = await this.authenticatedUpload('/media/upload', formData);
              if (!uploadResult.success) {
                throw new Error(uploadResult.message || 'Failed to upload media');
            }
            
            return uploadResult.file_path;
            
        } catch (error) {
            console.error('Media upload error:', error);
            throw new Error('Failed to upload media: ' + error.message);
        }
    }    async createPost(postType) {
        // Create FormData for the request
        const formData = new FormData();
        
        // Add basic post data
        formData.append('post_type', postType);
        formData.append('privacy_level', this.privacySelect.value);
        formData.append('caption', this.captionTextarea.value.trim());
        
        // Add location data if present
        if (this.locationData) {
            formData.append('location_name', this.locationData.name || '');
            if (this.locationData.lat) formData.append('location_lat', this.locationData.lat);
            if (this.locationData.lng) formData.append('location_lng', this.locationData.lng);
        }
        
        // Add media file if present
        if (this.mediaData && (postType === 'photo' || postType === 'video')) {
            try {
                let blob;                // Get blob based on storage method
                if (this.mediaData.storageMethod === 'indexedDB') {
                    console.log('Retrieving video from IndexedDB...');
                    blob = await this.getMediaBlobFromIndexedDB();
                } else if (this.mediaData.dataUrl) {
                    console.log('Converting dataUrl to blob...');
                    const response = await fetch(this.mediaData.dataUrl);
                    blob = await response.blob();
                } else {
                    throw new Error('No media data available');
                }
                
                // Convert video to MP4 for better compatibility
                if (postType === 'video' && blob.type.includes('webm')) {
                    console.log('Converting video to MP4 for better compatibility...');
                    try {
                        blob = await this.convertVideoToMP4(blob);
                        console.log('Video conversion completed:', blob.type);
                    } catch (error) {
                        console.warn('Video conversion failed, using original format:', error);
                        // Continue with original blob if conversion fails
                    }
                }
                
                // Create proper filename with correct extension based on blob type
                let extension = 'jpg'; // default for photos
                if (postType === 'video') {
                    // Determine extension based on actual blob type
                    if (blob.type.includes('mp4')) {
                        extension = 'mp4';
                    } else if (blob.type.includes('webm')) {
                        extension = 'webm';
                    } else {
                        extension = 'mp4'; // default for videos (assuming conversion succeeded)
                    }
                }
                
                const filename = `media_${Date.now()}.${extension}`;
                formData.append('media', blob, filename);
                
                console.log('Media file prepared:', {
                    filename,
                    size: blob.size,
                    type: blob.type
                });
            } catch (error) {
                console.error('Error preparing media file:', error);
                throw new Error('Failed to prepare media file for upload');
            }
        }try {
            let response;
            
            // Check if authenticatedUpload method exists, otherwise use authenticatedRequest
            if (typeof this.apiHandler.authenticatedUpload === 'function') {
                response = await this.apiHandler.authenticatedUpload('/posts/create_post', formData);            } else {
                // Fallback: make direct fetch request for FormData upload
                const token = this.apiHandler.getAuthToken();
                if (!token) {
                    throw new Error('No authentication token found');
                }
                
                const url = `${this.apiHandler.baseURL}/posts/create_post.php`;
                const fetchResponse = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`
                        // No Content-Type header - let browser set multipart/form-data boundary
                    },
                    body: formData
                });
                
                const contentType = fetchResponse.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    response = await fetchResponse.json();
                } else {
                    const text = await fetchResponse.text();
                    try {
                        response = JSON.parse(text);
                    } catch {
                        response = { success: false, message: text };
                    }
                }
                
                if (!fetchResponse.ok && !response.message) {
                    throw new Error(`HTTP error! status: ${fetchResponse.status}`);
                }
            }
            
            if (!response.success) {
                throw new Error(response.message || 'Failed to create post');
            }
            
            return response;
              } catch (error) {
            console.error('Create post error:', error);
            throw new Error('Failed to create post: ' + error.message);
        }
    }
      // Get media blob from IndexedDB for videos
    async getMediaBlobFromIndexedDB() {
        return new Promise((resolve, reject) => {
            const dbRequest = indexedDB.open('SocialConnectMedia', 1);
            
            dbRequest.onerror = () => {
                console.error('IndexedDB error:', dbRequest.error);
                reject(new Error('Failed to open IndexedDB: ' + dbRequest.error));
            };
            
            dbRequest.onsuccess = (event) => {
                const db = event.target.result;
                const transaction = db.transaction(['media'], 'readonly');
                const store = transaction.objectStore('media');
                
                const getRequest = store.get(this.mediaData.filename);
                
                getRequest.onsuccess = () => {
                    const mediaRecord = getRequest.result;
                    if (mediaRecord && mediaRecord.blob) {
                        console.log('Media blob retrieved from IndexedDB successfully');
                        resolve(mediaRecord.blob);
                    } else {
                        console.error('Media not found in IndexedDB for filename:', this.mediaData.filename);
                        reject(new Error('Media not found in IndexedDB'));
                    }
                };
                
                getRequest.onerror = () => {
                    console.error('Error retrieving media from IndexedDB:', getRequest.error);
                    reject(new Error('Failed to retrieve media from IndexedDB'));
                };
                
                transaction.oncomplete = () => {
                    db.close();
                };
                
                transaction.onerror = () => {
                    console.error('IndexedDB transaction error:', transaction.error);
                    reject(new Error('IndexedDB transaction failed'));
                };
            };
        });
    }
      // Convert video blob to MP4 format for better compatibility
    async convertVideoToMP4(inputBlob) {
        return new Promise((resolve, reject) => {
            try {
                console.log('Checking MP4 conversion options...');
                
                // Check if browser supports MP4 recording
                const mimeTypes = [
                    'video/mp4',
                    'video/mp4;codecs=avc1',
                    'video/mp4;codecs=h264',
                    'video/webm;codecs=h264'
                ];
                
                let supportedMimeType = null;
                for (const mimeType of mimeTypes) {
                    if (MediaRecorder.isTypeSupported(mimeType)) {
                        supportedMimeType = mimeType;
                        console.log('Found supported MIME type:', mimeType);
                        break;
                    }
                }
                
                if (!supportedMimeType) {
                    console.log('No MP4 conversion possible, using original WebM');
                    // Return original blob if conversion not possible
                    resolve(inputBlob);
                    return;
                }
                
                // Create video element to load the source
                const video = document.createElement('video');
                video.muted = true;
                video.playsInline = true;
                
                // Create canvas for video frames
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                video.onloadedmetadata = () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    
                    // Create MediaRecorder to record the canvas stream
                    const stream = canvas.captureStream(30); // 30 FPS
                    
                    // Add audio track from original video if possible
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const source = audioContext.createMediaElementSource(video);
                    const destination = audioContext.createMediaStreamDestination();
                    source.connect(destination);
                    
                    // Combine video and audio streams
                    const combinedStream = new MediaStream([
                        ...stream.getVideoTracks(),
                        ...destination.stream.getAudioTracks()
                    ]);
                    
                    const recorder = new MediaRecorder(combinedStream, {
                        mimeType: supportedMimeType,
                        videoBitsPerSecond: 1000000 // 1 Mbps
                    });
                    
                    const chunks = [];
                    
                    recorder.ondataavailable = (event) => {
                        if (event.data.size > 0) {
                            chunks.push(event.data);
                        }
                    };
                    
                    recorder.onstop = () => {
                        const convertedBlob = new Blob(chunks, { type: supportedMimeType });
                        console.log('Video conversion completed:', {
                            originalSize: inputBlob.size,
                            convertedSize: convertedBlob.size,
                            originalType: inputBlob.type,
                            convertedType: convertedBlob.type
                        });
                        resolve(convertedBlob);
                    };
                    
                    recorder.onerror = (error) => {
                        console.error('MediaRecorder error:', error);
                        // Fallback to original blob on error
                        resolve(inputBlob);
                    };
                    
                    // Function to draw video frames to canvas
                    const drawFrame = () => {
                        if (!video.paused && !video.ended) {
                            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                            requestAnimationFrame(drawFrame);
                        }
                    };
                    
                    // Start recording and play video
                    recorder.start();
                    video.currentTime = 0;
                    video.play();
                    drawFrame();
                    
                    // Stop recording when video ends
                    video.onended = () => {
                        recorder.stop();
                        audioContext.close();
                    };
                };
                
                video.onerror = (error) => {
                    console.error('Video loading error:', error);
                    // Fallback to original blob on error
                    resolve(inputBlob);
                };
                
                // Load the video blob
                video.src = URL.createObjectURL(inputBlob);
                
            } catch (error) {
                console.error('Video conversion setup error:', error);
                // Fallback to original blob on any error
                resolve(inputBlob);
            }
        });
    }
    
    showLoadingState(show) {
        this.createPostButton.style.display = show ? 'none' : 'flex';
        this.loadingState.style.display = show ? 'flex' : 'none';
    }
    
    showStatusMessage(message, type = 'info') {
        const messageElement = document.createElement('div');
        messageElement.className = `status-message ${type}`;
        
        // Add icon based on type
        const iconSvg = this.getStatusIcon(type);
        messageElement.innerHTML = `
            ${iconSvg}
            <span>${message}</span>
        `;
        
        this.statusMessages.appendChild(messageElement);
        this.statusMessages.style.display = 'block';
        
        // Auto-remove success messages
        if (type === 'success') {
            setTimeout(() => {
                if (messageElement.parentNode) {
                    messageElement.remove();
                    if (this.statusMessages.children.length === 0) {
                        this.statusMessages.style.display = 'none';
                    }
                }
            }, 5000);
        }
    }
    
    getStatusIcon(type) {
        const icons = {
            success: '<svg class="status-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            error: '<svg class="status-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/><line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/></svg>',
            warning: '<svg class="status-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="2"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2"/></svg>'
        };
        return icons[type] || icons.info;
    }
    
    clearStatusMessages() {
        this.statusMessages.innerHTML = '';
        this.statusMessages.style.display = 'none';
    }
    
    resetForm() {
        // Clear caption
        this.captionTextarea.value = '';
        this.updateCharacterCount();
        
        // Reset privacy to default
        this.privacySelect.value = 'public';
        
        // Clear session storage
        sessionStorage.removeItem('capturedMedia');
        sessionStorage.removeItem('selectedLocation');
          // Reset state
        this.mediaData = null;
        this.locationData = null;
        
        // Reinitialize sub-components to reset their state
        setTimeout(() => {
            this.initializeSubComponents();
        }, 500);
        
        this.validateForm();
    }
    
    loadInitialData() {
        // Check for existing media and location data
        this.checkMediaData();
        this.checkLocationData();
        this.validateForm();
    }
    
    destroy() {
        // Clean up intervals
        if (this.mediaCheckInterval) {
            clearInterval(this.mediaCheckInterval);
        }
        if (this.locationCheckInterval) {
            clearInterval(this.locationCheckInterval);
        }
    }
}

// Global initialization function
function initializeCreatePostContainer() {
    // Ensure APIHandler is available
    if (typeof APIHandler === 'undefined') {
        // Load the generic API handler
        const script = document.createElement('script');
        script.src = '../js/generic-api.js';
        script.onload = () => {
            // Initialize after API handler is loaded
            setTimeout(() => {
                if (document.querySelector('.create-post-container')) {
                    window.createPostContainer = new CreatePostContainer();
                }
            }, 100);
        };
        document.head.appendChild(script);
    } else {
        // Initialize immediately
        if (document.querySelector('.create-post-container')) {
            window.createPostContainer = new CreatePostContainer();
        }
    }
}

// Export for potential use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CreatePostContainer;
}
