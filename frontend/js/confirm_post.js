// SocialConnect Confirm Post Page Handler
// Handles post creation with media, captions, location, and visibility settings

class ConfirmPost {
    constructor() {
        // DOM elements
        this.postForm = document.getElementById('postForm');
        this.mediaPreviewSection = document.getElementById('mediaPreviewSection');
        this.noMediaState = document.getElementById('noMediaState');
        
        // Media preview elements
        this.mediaPreviewImage = document.getElementById('mediaPreviewImage');
        this.mediaPreviewVideo = document.getElementById('mediaPreviewVideo');
        this.mediaInfo = document.getElementById('mediaInfo');
        this.mediaType = document.getElementById('mediaType');
        this.mediaSize = document.getElementById('mediaSize');
        this.changeMediaBtn = document.getElementById('changeMediaBtn');
        
        // Form elements
        this.postCaption = document.getElementById('postCaption');
        this.characterCount = document.getElementById('characterCount');
        this.postLocation = document.getElementById('postLocation');
        this.locationBtn = document.getElementById('locationBtn');
        this.postVisibility = document.getElementById('postVisibility');
        
        // Action buttons
        this.backBtn = document.getElementById('backBtn');
        this.shareBtn = document.getElementById('shareBtn');
        this.addMediaBtn = document.getElementById('addMediaBtn');
        this.shareText = this.shareBtn.querySelector('.share-text');
        this.shareIcon = this.shareBtn.querySelector('.share-icon');
        this.loadingSpinner = this.shareBtn.querySelector('.loading-spinner');
        
        // State
        this.mediaData = null;
        this.isSubmitting = false;
        this.geolocationWatcher = null;
        
        this.init();
    }
    
    init() {
        this.loadMediaData();
        this.bindEvents();
        this.updateCharacterCount();
    }
    
    bindEvents() {
        // Form submission
        this.postForm.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Caption character count
        this.postCaption.addEventListener('input', () => this.updateCharacterCount());
        
        // Location services
        this.locationBtn.addEventListener('click', () => this.getCurrentLocation());
        
        // Navigation buttons
        this.backBtn.addEventListener('click', () => this.goBackToCamera());
        this.changeMediaBtn.addEventListener('click', () => this.goBackToCamera());
        this.addMediaBtn.addEventListener('click', () => this.goBackToCamera());
        
        // Form validation
        this.postCaption.addEventListener('input', () => this.validateForm());
        this.postLocation.addEventListener('input', () => this.validateForm());
        this.postVisibility.addEventListener('change', () => this.validateForm());
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyDown(e));
    }
      async loadMediaData() {
        try {
            const mediaDataString = sessionStorage.getItem('capturedMedia');
            if (mediaDataString) {
                this.mediaData = JSON.parse(mediaDataString);
                
                // Check storage method and load accordingly
                if (this.mediaData.storageMethod === 'indexedDB') {
                    console.log('Loading media from IndexedDB...');
                    await this.loadFromIndexedDB();
                } else {
                    console.log('Loading media from sessionStorage...');
                    // Media data URL is already in this.mediaData
                }
                
                this.displayMedia();
                this.showMediaState();
            } else {
                this.showNoMediaState();
            }
        } catch (error) {
            console.error('Error loading media data:', error);
            this.showNoMediaState();
        }
    }
    
    async loadFromIndexedDB() {
        return new Promise((resolve, reject) => {
            const dbRequest = indexedDB.open('SocialConnectMedia', 1);
            
            dbRequest.onerror = () => {
                console.error('IndexedDB error:', dbRequest.error);
                reject(dbRequest.error);
            };
            
            dbRequest.onsuccess = (event) => {
                const db = event.target.result;
                const transaction = db.transaction(['media'], 'readonly');
                const store = transaction.objectStore('media');
                
                const getRequest = store.get(this.mediaData.filename);
                
                getRequest.onsuccess = () => {
                    const mediaRecord = getRequest.result;
                    if (mediaRecord) {
                        // Create object URL for the blob
                        this.mediaData.dataUrl = URL.createObjectURL(mediaRecord.blob);
                        this.mediaData.blob = mediaRecord.blob; // Keep reference to original blob
                        console.log('Media loaded from IndexedDB successfully');
                        resolve();
                    } else {
                        console.error('Media not found in IndexedDB');
                        reject(new Error('Media not found'));
                    }
                };
                
                getRequest.onerror = () => {
                    console.error('Error loading media from IndexedDB:', getRequest.error);
                    reject(getRequest.error);
                };
                
                transaction.oncomplete = () => {
                    db.close();
                };
            };
        });
    }
    
    displayMedia() {
        if (!this.mediaData) return;
        
        // Display media preview
        if (this.mediaData.type === 'image') {
            this.mediaPreviewImage.src = this.mediaData.dataUrl;
            this.mediaPreviewImage.style.display = 'block';
            this.mediaPreviewVideo.style.display = 'none';
        } else {
            this.mediaPreviewVideo.src = this.mediaData.dataUrl;
            this.mediaPreviewVideo.style.display = 'block';
            this.mediaPreviewImage.style.display = 'none';
        }
        
        // Display media info
        this.mediaType.textContent = this.mediaData.type === 'image' ? 'Photo' : 'Video';
        this.mediaSize.textContent = this.formatFileSize(this.mediaData.size);
        this.mediaInfo.style.display = 'flex';
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    showMediaState() {
        this.mediaPreviewSection.style.display = 'block';
        this.postForm.style.display = 'block';
        this.noMediaState.style.display = 'none';
        this.validateForm();
    }
    
    showNoMediaState() {
        this.mediaPreviewSection.style.display = 'none';
        this.postForm.style.display = 'none';
        this.noMediaState.style.display = 'flex';
    }
    
    updateCharacterCount() {
        const currentLength = this.postCaption.value.length;
        const maxLength = 500;
        
        this.characterCount.textContent = `${currentLength}/${maxLength}`;
        
        // Update styling based on character count
        this.characterCount.classList.remove('warning', 'error');
        if (currentLength > maxLength * 0.9) {
            this.characterCount.classList.add('warning');
        }
        if (currentLength > maxLength) {
            this.characterCount.classList.add('error');
        }
        
        this.validateForm();
    }
    
    validateForm() {
        const hasMedia = this.mediaData !== null;
        const captionValid = this.postCaption.value.length <= 500;
        const notSubmitting = !this.isSubmitting;
        
        const isValid = hasMedia && captionValid && notSubmitting;
        this.shareBtn.disabled = !isValid;
    }
    
    async getCurrentLocation() {
        if (!navigator.geolocation) {
            this.showLocationError('Geolocation is not supported by this browser.');
            return;
        }
        
        this.locationBtn.disabled = true;
        this.locationBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12a9 9 0 11-6.219-8.56"/>
            </svg>
        `;
        
        try {
            const position = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                });
            });
            
            const { latitude, longitude } = position.coords;
            
            // In a real app, you'd reverse geocode these coordinates
            // For now, we'll just show a formatted location
            const locationString = `${latitude.toFixed(4)}, ${longitude.toFixed(4)}`;
            this.postLocation.value = locationString;
            
            this.showLocationSuccess('Location added successfully');
            
        } catch (error) {
            console.error('Geolocation error:', error);
            let errorMessage = 'Unable to get location. ';
            
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage += 'Location access denied.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += 'Location information unavailable.';
                    break;
                case error.TIMEOUT:
                    errorMessage += 'Location request timed out.';
                    break;
                default:
                    errorMessage += 'An unknown error occurred.';
                    break;
            }
            
            this.showLocationError(errorMessage);
        } finally {
            this.locationBtn.disabled = false;
            this.locationBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="10" r="3"></circle>
                    <path d="m12 21.7-6.3-6.3a9 9 0 1 1 12.6 0L12 21.7z"></path>
                </svg>
            `;
        }
    }
    
    showLocationSuccess(message) {
        this.showFormMessage(message, 'success');
    }
    
    showLocationError(message) {
        this.showFormMessage(message, 'error');
    }
    
    showFormMessage(message, type) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.form-message');
        existingMessages.forEach(msg => msg.remove());
        
        // Create new message
        const messageEl = document.createElement('div');
        messageEl.className = `form-message ${type}`;
        messageEl.textContent = message;
        
        // Insert before form actions
        const formActions = document.querySelector('.form-actions');
        formActions.parentNode.insertBefore(messageEl, formActions);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (messageEl.parentNode) {
                messageEl.remove();
            }
        }, 5000);
    }
    
    async handleSubmit(event) {
        event.preventDefault();
        
        if (this.isSubmitting || !this.mediaData) return;
        
        this.isSubmitting = true;
        this.updateSubmitButton(true);
        
        try {
            // Prepare post data
            const postData = {
                caption: this.postCaption.value.trim(),
                location: this.postLocation.value.trim(),
                visibility: this.postVisibility.value,
                media: {
                    filename: this.mediaData.filename,
                    type: this.mediaData.type,
                    size: this.mediaData.size,
                    dataUrl: this.mediaData.dataUrl
                },
                timestamp: Date.now()
            };
            
            // Simulate API call (replace with actual API endpoint)
            await this.simulatePostSubmission(postData);
            
            // Clear media data from session
            sessionStorage.removeItem('capturedMedia');
            
            // Show success and redirect
            this.showFormMessage('Post shared successfully!', 'success');
            
            setTimeout(() => {
                // Redirect to feed or user profile
                window.location.href = 'feed.php';
            }, 1500);
            
        } catch (error) {
            console.error('Error submitting post:', error);
            this.showFormMessage('Failed to share post. Please try again.', 'error');
        } finally {
            this.isSubmitting = false;
            this.updateSubmitButton(false);
        }
    }
    
    async simulatePostSubmission(postData) {
        // Simulate network delay
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // In a real app, this would make an API call to your backend
        console.log('Post data to submit:', postData);
        
        // Simulate random failure for demo
        if (Math.random() < 0.1) {
            throw new Error('Network error');
        }
    }
    
    updateSubmitButton(isLoading) {
        if (isLoading) {
            this.shareText.style.display = 'none';
            this.shareIcon.style.display = 'none';
            this.loadingSpinner.style.display = 'block';
            this.shareBtn.disabled = true;
        } else {
            this.shareText.style.display = 'block';
            this.shareIcon.style.display = 'block';
            this.loadingSpinner.style.display = 'none';
            this.validateForm(); // Re-enable based on form validation
        }
    }
    
    goBackToCamera() {
        // Optional: Clear media data if user wants to start over
        // sessionStorage.removeItem('capturedMedia');
        
        window.location.href = '../components/camera_capture.html';
    }
    
    handleKeyDown(event) {
        // Escape key to go back
        if (event.key === 'Escape' && !this.isSubmitting) {
            this.goBackToCamera();
        }
        
        // Ctrl/Cmd + Enter to submit
        if ((event.ctrlKey || event.metaKey) && event.key === 'Enter' && !this.shareBtn.disabled) {
            event.preventDefault();
            this.handleSubmit(event);
        }
    }
    
    cleanup() {
        // Clean up any intervals or watchers
        if (this.geolocationWatcher) {
            navigator.geolocation.clearWatch(this.geolocationWatcher);
        }
        
        // Clean up object URLs
        if (this.mediaPreviewImage.src) {
            URL.revokeObjectURL(this.mediaPreviewImage.src);
        }
        if (this.mediaPreviewVideo.src) {
            URL.revokeObjectURL(this.mediaPreviewVideo.src);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ConfirmPost();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    const confirmPost = window.confirmPost;
    if (confirmPost) {
        confirmPost.cleanup();
    }
});
