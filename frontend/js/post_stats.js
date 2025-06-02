/**
 * Post Stats Component JavaScript
 * Handles like/unlike functionality and comment navigation
 */

// Initialize API handler only if not already defined
if (typeof window.postStatsAPI === 'undefined') {
    window.postStatsAPI = new APIHandler();
}

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializePostStats();
    });
} else {
    initializePostStats();
}

function initializePostStats() {
    // Add keyboard accessibility for stat buttons
    const statButtons = document.querySelectorAll('.stat-button');
    statButtons.forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                button.click();
            }
        });
    });
}

/**
 * Navigate to comments page for a specific post
 * @param {string} postId - The ID of the post
 */
window.navigateToComments = function(postId) {
    if (!postId) {
        console.error('Post ID is required to navigate to comments');
        return;
    }
    
    // Redirect to comments page with post ID
    window.location.href = `view_post.php?post_id=${postId}`;
}

/**
 * Toggle like status for a post
 * @param {string} postId - The ID of the post to like/unlike
 */
window.toggleLike = async function(postId) {
    if (!postId) {
        console.error('Post ID is required to toggle like');
        return;
    }

    const likeButton = document.querySelector(`.like-button[data-post-id="${postId}"]`);
    const likeCountElement = document.getElementById(`like-count-${postId}`);
    
    if (!likeButton || !likeCountElement) {
        console.error('Like button or count element not found');
        return;
    }

    // Get current like status from the button's data attribute
    const isCurrentlyLiked = likeButton.getAttribute('data-liked') === '1';
    const currentCount = parseInt(likeCountElement.textContent) || 0;

    // Add loading state
    likeButton.classList.add('loading');
    likeButton.disabled = true;

    try {
        let response;
        let endpoint;
        let expectedNewCount;
        
        if (isCurrentlyLiked) {
            // Unlike the post
            endpoint = '/posts/unlike_post';
            expectedNewCount = Math.max(0, currentCount - 1);
        } else {
            // Like the post
            endpoint = '/posts/like_post';
            expectedNewCount = currentCount + 1;
        }

        response = await window.postStatsAPI.authenticatedRequest(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ post_id: parseInt(postId) })
        });

        // Check if the API call was successful
        if (response && response.success) {
            // Update UI with server response count if available, otherwise use calculated count
            const serverCount = response.likes_count !== undefined ? response.likes_count : expectedNewCount;
            const newLikedState = !isCurrentlyLiked;
            
            window.updateLikeUI(postId, newLikedState, serverCount);
            
            // Show success feedback (optional)
            showFeedback(likeButton, isCurrentlyLiked ? 'Unliked' : 'Liked', 'success');
        } else {
            throw new Error(response?.message || 'Failed to update like status');
        }

    } catch (error) {
        console.error('Error toggling like:', error);
        
        // Show error feedback
        showFeedback(likeButton, 'Error: ' + error.message, 'error');
        
        // UI stays the same since we haven't updated it yet
    } finally {
        // Remove loading state
        likeButton.classList.remove('loading');
        likeButton.disabled = false;
    }
}

/**
 * Update the like button UI and count
 * @param {string} postId - The post ID
 * @param {boolean} isLiked - Whether the post is now liked
 * @param {number} likeCount - The new like count
 */
window.updateLikeUI = function(postId, isLiked, likeCount) {
    const likeButton = document.querySelector(`.like-button[data-post-id="${postId}"]`);
    const likeCountElement = document.getElementById(`like-count-${postId}`);
    
    if (!likeButton || !likeCountElement) {
        console.error('Like button or count element not found for update');
        return;
    }

    // Update like status
    likeButton.setAttribute('data-liked', isLiked ? '1' : '0');
    
    // Update aria-label for accessibility
    likeButton.setAttribute('aria-label', isLiked ? 'Unlike this post' : 'Like this post');
    
    // Update count
    likeCountElement.textContent = likeCount;

    // Add visual feedback animation
    likeCountElement.style.transform = 'scale(1.2)';
    setTimeout(() => {
        likeCountElement.style.transform = 'scale(1)';
    }, 150);
}

/**
 * Show temporary feedback message
 * @param {Element} element - The element to show feedback near
 * @param {string} message - The feedback message
 * @param {string} type - The type of feedback ('success' or 'error')
 */
function showFeedback(element, message, type = 'success') {
    // Remove any existing feedback
    const existingFeedback = element.querySelector('.feedback-message');
    if (existingFeedback) {
        existingFeedback.remove();
    }

    // Create feedback element
    const feedback = document.createElement('span');
    feedback.className = `feedback-message feedback-${type}`;
    feedback.textContent = message;
    feedback.style.cssText = `
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        background: ${type === 'success' ? 'var(--color-success)' : 'var(--color-error)'};
        color: white;
        padding: 4px 8px;
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-caption);
        white-space: nowrap;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
    `;

    // Position relative to button
    element.style.position = 'relative';
    element.appendChild(feedback);

    // Animate in
    setTimeout(() => {
        feedback.style.opacity = '1';
    }, 10);

    // Remove after delay
    setTimeout(() => {
        feedback.style.opacity = '0';
        setTimeout(() => {
            if (feedback.parentNode) {
                feedback.remove();
            }
        }, 200);
    }, 2000);
}

/**
 * Handle authentication errors
 * @param {Error} error - The authentication error
 */
function handleAuthError(error) {
    console.error('Authentication error:', error);
    
    // Redirect to login if not authenticated
    if (error.message.includes('authentication') || error.message.includes('token')) {
        window.location.href = 'login.php';
    }
}
