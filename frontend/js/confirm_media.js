// SocialConnect Confirm Media Component Handler
// Handles media display for the confirm_media component

class ConfirmMediaDisplay {
    constructor() {
        // Media preview elements
        this.mediaPreviewSection = document.getElementById('mediaPreviewSection');
        this.noMediaState = document.getElementById('noMediaState');
        this.mediaPreviewImage = document.getElementById('mediaPreviewImage');
        this.mediaPreviewVideo = document.getElementById('mediaPreviewVideo');
        this.mediaInfo = document.getElementById('mediaInfo');
        this.mediaType = document.getElementById('mediaType');
        this.mediaSize = document.getElementById('mediaSize');
          // State
        this.mediaData = null;
        this.videoRetryAttempted = false;
        
        this.init();
    }
    
    init() {
        this.loadMediaData();
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
                        
                        console.log('Media loaded from IndexedDB successfully', {
                            type: mediaRecord.type,
                            size: mediaRecord.size,
                            blobType: mediaRecord.blob.type
                        });
                        
                        // For videos, let's also try to validate the blob
                        if (mediaRecord.type === 'video') {
                            console.log('Video blob details:', {
                                type: mediaRecord.blob.type,
                                size: mediaRecord.blob.size
                            });
                        }
                        
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
        
        console.log('Displaying media:', this.mediaData);
        
        // Display media preview
        if (this.mediaData.type === 'image') {
            this.mediaPreviewImage.src = this.mediaData.dataUrl;
            this.mediaPreviewImage.style.display = 'block';
            this.mediaPreviewVideo.style.display = 'none';
        } else {
            // Video handling with better error handling and debugging
            console.log('Setting up video element with URL:', this.mediaData.dataUrl);
            
            // Reset video element
            this.mediaPreviewVideo.load();
            
            // Add event listeners for debugging
            this.mediaPreviewVideo.onloadstart = () => console.log('Video: loadstart');
            this.mediaPreviewVideo.onloadedmetadata = () => {
                console.log('Video: loadedmetadata', {
                    duration: this.mediaPreviewVideo.duration,
                    videoWidth: this.mediaPreviewVideo.videoWidth,
                    videoHeight: this.mediaPreviewVideo.videoHeight
                });
            };
            this.mediaPreviewVideo.onloadeddata = () => console.log('Video: loadeddata');
            this.mediaPreviewVideo.oncanplay = () => console.log('Video: canplay');
            this.mediaPreviewVideo.oncanplaythrough = () => console.log('Video: canplaythrough');            this.mediaPreviewVideo.onerror = (e) => {
                console.error('Video error:', e, this.mediaPreviewVideo.error);
                
                // Try to reload the video with different settings
                if (!this.videoRetryAttempted) {
                    console.log('Attempting video reload with different settings...');
                    this.videoRetryAttempted = true;
                    this.retryVideoPlayback();
                } else {
                    this.showVideoError();
                }
            };
            
            // Set video source and properties
            this.mediaPreviewVideo.src = this.mediaData.dataUrl;
            this.mediaPreviewVideo.preload = 'metadata';
            this.mediaPreviewVideo.muted = false; // Allow audio
            this.mediaPreviewVideo.controls = true;
            
            // Show video element
            this.mediaPreviewVideo.style.display = 'block';
            this.mediaPreviewImage.style.display = 'none';
            
            // Force video to load
            this.mediaPreviewVideo.load();
        }
        
        // Display media info
        this.mediaType.textContent = this.mediaData.type === 'image' ? 'Photo' : 'Video';
        this.mediaSize.textContent = this.formatFileSize(this.mediaData.size);
        this.mediaInfo.style.display = 'flex';
    }
      showVideoError() {
        // Check if video element exists before trying to replace it
        if (!this.mediaPreviewVideo || !this.mediaPreviewVideo.parentNode) {
            console.error('Video element not found for error display');
            return;
        }
        
        // Create error message in place of video
        const errorDiv = document.createElement('div');
        errorDiv.className = 'video-error';
        errorDiv.innerHTML = `
            <div style="
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 200px;
                background: #f5f5f5;
                border: 2px dashed #ccc;
                border-radius: 8px;
                color: #666;
                text-align: center;
                padding: 20px;
            ">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m22 13.29-3.33-10a1.59 1.59 0 0 0-3 0L12.34 13.29a1.59 1.59 0 0 0 1.5 2.21h6.66a1.59 1.59 0 0 0 1.5-2.21z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <h4 style="margin: 10px 0 5px 0;">Video Playback Error</h4>
                <p style="margin: 0; font-size: 14px;">The video format may not be supported by your browser</p>
            </div>
        `;
          // Replace video element with error message
        this.mediaPreviewVideo.parentNode.replaceChild(errorDiv, this.mediaPreviewVideo);
        
        // Update reference to prevent further errors
        this.mediaPreviewVideo = null;
    }
      retryVideoPlayback() {
        try {
            // Check if video element still exists
            if (!this.mediaPreviewVideo) {
                console.warn('Cannot retry video playback - video element was replaced with error message');
                return;
            }
            
            // Reset video element completely
            this.mediaPreviewVideo.pause();
            this.mediaPreviewVideo.removeAttribute('src');
            this.mediaPreviewVideo.load();
            
            // Try different video settings
            this.mediaPreviewVideo.preload = 'auto';
            this.mediaPreviewVideo.muted = true; // Start muted for autoplay compatibility
            
            // Set source again
            this.mediaPreviewVideo.src = this.mediaData.dataUrl;
            this.mediaPreviewVideo.load();
            
            // Try to play after a short delay
            setTimeout(() => {
                if (this.mediaPreviewVideo) {
                    this.mediaPreviewVideo.play().catch(error => {
                        console.error('Video play failed:', error);
                        this.showVideoError();
                    });
                }
            }, 500);
            
        } catch (error) {
            console.error('Video retry failed:', error);
            this.showVideoError();
        }
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    showMediaState() {
        if (this.mediaPreviewSection) this.mediaPreviewSection.style.display = 'block';
        if (this.noMediaState) this.noMediaState.style.display = 'none';
    }
    
    showNoMediaState() {
        if (this.mediaPreviewSection) this.mediaPreviewSection.style.display = 'none';
        if (this.noMediaState) this.noMediaState.style.display = 'flex';
    }
    
    cleanup() {
        // Clean up object URLs
        if (this.mediaPreviewImage && this.mediaPreviewImage.src) {
            URL.revokeObjectURL(this.mediaPreviewImage.src);
        }
        if (this.mediaPreviewVideo && this.mediaPreviewVideo.src) {
            URL.revokeObjectURL(this.mediaPreviewVideo.src);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ConfirmMediaDisplay();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    const confirmMediaDisplay = window.confirmMediaDisplay;
    if (confirmMediaDisplay) {
        confirmMediaDisplay.cleanup();
    }
});
