
/**
 * Post Media Component JavaScript
 * Simple and reliable media handling
 */

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializePostMedia();
});

function initializePostMedia() {
    // Get all post media images on the page
    const mediaImages = document.querySelectorAll('.post-media-image');
    
    mediaImages.forEach(function(img) {
        // If image fails to load, try as video
        img.addEventListener('error', function() {
            tryAsVideo(this);
        });
          // If image loads successfully, ensure it's visible and hide any errors
        img.addEventListener('load', function() {
            this.style.display = 'block';
            const mediaWrapper = this.closest('.media-wrapper');
            if (mediaWrapper) {
                hideError(mediaWrapper);
            }
        });
    });
}

function tryAsVideo(failedImg) {
    const mediaWrapper = failedImg.closest('.media-wrapper');
    if (!mediaWrapper) return;
    
    const videoElement = mediaWrapper.querySelector('.post-media-video');
    const errorElement = mediaWrapper.querySelector('.media-error');
    
    if (!videoElement) {
        showError(mediaWrapper);
        return;
    }
    
    // Hide the failed image
    failedImg.style.display = 'none';
    
    // Set up the video
    const source = videoElement.querySelector('source');
    if (source) {
        source.src = failedImg.src;
        
        // Determine video type from URL
        const extension = getFileExtension(failedImg.src).toLowerCase();
        const mimeTypes = {
            'mp4': 'video/mp4',
            'webm': 'video/webm',
            'ogg': 'video/ogg',
            'mov': 'video/quicktime'
        };
        source.type = mimeTypes[extension] || 'video/mp4';
    }
      // Hide any existing error
    hideError(mediaWrapper);
    
    // Show video and handle its events
    videoElement.style.display = 'block';
    
    // Handle video load success
    videoElement.addEventListener('loadedmetadata', function() {
        hideError(mediaWrapper);
        this.style.display = 'block';
    }, { once: true });
    
    videoElement.addEventListener('canplay', function() {
        hideError(mediaWrapper);
        this.style.display = 'block';
    }, { once: true });
    
    // Handle video load error
    videoElement.addEventListener('error', function() {
        this.style.display = 'none';
        showError(mediaWrapper);
    }, { once: true });
    
    // Load the video
    videoElement.load();
}

function showError(mediaWrapper) {
    const errorElement = mediaWrapper.querySelector('.media-error');
    if (errorElement) {
        errorElement.style.display = 'flex';
    }
}

function hideError(mediaWrapper) {
    const errorElement = mediaWrapper.querySelector('.media-error');
    if (errorElement) {
        errorElement.style.display = 'none';
    }
}

function getFileExtension(url) {
    if (!url) return '';
    const cleanUrl = url.split('?')[0].split('#')[0];
    const parts = cleanUrl.split('.');
    return parts.length > 1 ? parts.pop() : '';
}
