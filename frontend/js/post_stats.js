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

    // Listen for comment added events to update comment count
    document.addEventListener('commentAdded', handleCommentAdded);
    
    // Listen for hide/show comments toggle to ensure we can add comments even when hidden
    document.addEventListener('commentsVisibilityChanged', function(e) {
        // When comments become visible after adding a comment that couldn't be displayed
        // try to process any pending comments
        if (e.detail && !e.detail.isHidden && e.detail.postId) {
            console.log('Comments became visible for post:', e.detail.postId);
            
            // Check if we have any pending comments to add
            if (window.pendingComments && window.pendingComments[e.detail.postId]) {
                const pendingComments = window.pendingComments[e.detail.postId];
                window.pendingComments[e.detail.postId] = [];
                
                pendingComments.forEach(comment => {
                    addCommentToDOM(e.detail.postId, comment);
                });
            }
        }
    });
}

/**
 * Handle the commentAdded event by updating comment counts and displaying new comments
 * @param {CustomEvent} event - The commentAdded event
 */
function handleCommentAdded(event) {
    const { postId, comment } = event.detail;
    
    if (!postId) {
        console.error('Post ID is required to handle comment added event');
        return;
    }

    // Update comment count in post stats
    updateCommentCount(postId);
    
    // Initialize the pendingComments storage if it doesn't exist
    if (!window.pendingComments) {
        window.pendingComments = {};
    }
    
    // If comment data is provided, add it to the DOM
    if (comment) {
        addCommentToDOM(postId, comment);
    }
}

/**
 * Try to show the comments section for a post if it exists but is hidden
 * @param {string} postId - The ID of the post
 */
function tryToShowCommentsSection(postId) {
    // Look for any post-stats-and-caption-container that might have our post
    const postStatsContainers = document.querySelectorAll('.post-stats-and-caption-container');
    
    for (let container of postStatsContainers) {
        // Try to find the hidden comments section
        const commentsSection = container.querySelector('.third-content');
        if (commentsSection) {
            // Check if this section contains our post's comments
            const commentsList = commentsSection.querySelector(`.comments-list[data-post-id="${postId}"]`) || 
                                (commentsSection.getAttribute('data-stored-post-id') === postId ? commentsSection : null);
            
            if (commentsList || commentsSection.getAttribute('data-stored-post-id') === postId) {
                // Found the right container - now show it
                const hideButton = container.querySelector('.hide-comments-btn');
                if (hideButton) {
                    console.log('Found hidden comments section for post', postId, '- showing it');
                    
                    // Click the hide button to show comments
                    hideButton.click();
                    
                    // Dispatch an event to notify that comments are now visible
                    const event = new CustomEvent('commentsVisibilityChanged', { 
                        detail: { 
                            postId: postId,
                            isHidden: false
                        }
                    });
                    document.dispatchEvent(event);
                    return true;
                }
            }
        }
    }
    
    return false;
}

/**
 * Update the comment count for a post
 * @param {string} postId - The ID of the post
 */
function updateCommentCount(postId) {
    const commentCountElement = document.getElementById(`comment-count-${postId}`);
    
    if (commentCountElement) {
        const currentCount = parseInt(commentCountElement.textContent) || 0;
        const newCount = currentCount + 1;
        commentCountElement.textContent = newCount;
        
        // Add visual feedback animation
        commentCountElement.style.transform = 'scale(1.2)';
        setTimeout(() => {
            commentCountElement.style.transform = 'scale(1)';
        }, 150);
    }
}

/**
 * Add a new comment to the DOM
 * @param {string} postId - The ID of the post
 * @param {object} comment - The comment data
 */
function addCommentToDOM(postId, comment) {
    console.log('addCommentToDOM called with postId:', postId, 'comment:', comment);
    
    // Find the comments container for this post
    const commentsContainer = document.querySelector(`.comments-list[data-post-id="${postId}"]`);
    
    if (!commentsContainer) {
        console.log('Comments container not found for post:', postId);
        console.log('Available comments containers:', document.querySelectorAll('.comments-list'));
        console.log('All elements with data-post-id:', document.querySelectorAll('[data-post-id]'));
        
        // Try to show any hidden comments sections that might contain our container
        const postStatsContainers = document.querySelectorAll('.post-stats-and-caption-container');
        postStatsContainers.forEach(container => {
            const commentsSection = container.querySelector('.third-content');
            if (commentsSection) {
                // Make comments section visible temporarily to check for our container
                const originalDisplay = commentsSection.style.display;
                const originalMaxHeight = commentsSection.style.maxHeight;
                const originalOverflow = commentsSection.style.overflow;
                
                commentsSection.style.display = 'block';
                commentsSection.style.maxHeight = 'none';
                commentsSection.style.overflow = 'visible';
                
                // Check again for our container
                const hiddenCommentsContainer = commentsSection.querySelector(`.comments-list[data-post-id="${postId}"]`);
                if (hiddenCommentsContainer) {
                    // Found it! Keep it visible for now
                    console.log('Found comments container in hidden section');
                } else {
                    // No container found, revert styles
                    commentsSection.style.display = originalDisplay;
                    commentsSection.style.maxHeight = originalMaxHeight;
                    commentsSection.style.overflow = originalOverflow;
                }
            }
        });
          // Try one more time
        const retryContainer = document.querySelector(`.comments-list[data-post-id="${postId}"]`);
        if (!retryContainer) {
            console.log('Still could not find comments container after unhiding sections');
            
            // Store the comment for later processing when the comments section becomes visible
            if (!window.pendingComments[postId]) {
                window.pendingComments[postId] = [];
            }
            window.pendingComments[postId].push(comment);
            
            // Try to show the comments section if it exists but is hidden
            tryToShowCommentsSection(postId);
            return;
        }
    }

    // Create comment element
    const commentElement = createCommentElement(comment, postId);
    
    if (commentElement) {
        // Add comment to the beginning of the comments list
        commentsContainer.insertBefore(commentElement, commentsContainer.firstChild);
        
        // Add smooth entrance animation
        commentElement.style.opacity = '0';
        commentElement.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            commentElement.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            commentElement.style.opacity = '1';
            commentElement.style.transform = 'translateY(0)';
        }, 10);
    }
}

/**
 * Create a comment DOM element from comment data
 * @param {object} comment - The comment data
 * @param {string} postId - The ID of the post
 * @returns {Element|null} The created comment element
 */
function createCommentElement(comment, postId) {
    if (!comment || !comment.id) {
        console.error('Invalid comment data provided');
        return null;
    }

    // Create the main comment container
    const commentContainer = document.createElement('div');
    commentContainer.className = 'comment-container';
    commentContainer.setAttribute('role', 'article');
    commentContainer.setAttribute('aria-label', `Comment by ${comment.user_name}`);
    commentContainer.setAttribute('data-post-id', postId);
    commentContainer.setAttribute('data-comment-id', comment.id);

    // Create comment HTML structure
    commentContainer.innerHTML = `
        <div class="comment-header">
            <div class="comment-avatar">
                <img src="${comment.user_profile_picture_url || '../assets/images/default-profile.svg'}" 
                     alt="${comment.user_name}'s profile picture" 
                     class="profile-picture"
                     onerror="this.src='../assets/images/default-profile.svg'">
            </div>
            <div class="comment-user-info">
                <a href="profile.php?user_id=${comment.user_id}" class="username-link">
                    <span class="username">${escapeHtml(comment.user_name)}</span>
                </a>
                <span class="comment-time">Just now</span>
            </div>
        </div>
        <div class="comment-content">
            <p class="comment-text">${escapeHtml(comment.content)}</p>
        </div>
        <div class="comment-actions">
            <button class="comment-like-button" 
                    onclick="toggleCommentLike('${comment.id}')"
                    data-liked="0"
                    data-comment-id="${comment.id}"
                    aria-label="Like this comment">
                <svg class="like-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 14C19 14 17 16 12 21C7 16 5 14 5 14C3.5 12.5 3.5 9.5 5 8C6.5 6.5 9.5 6.5 11 8L12 9L13 8C14.5 6.5 17.5 6.5 19 8C20.5 9.5 20.5 12.5 19 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="like-count" id="comment-like-count-${comment.id}">0</span>
            </button>
            <button class="comment-reply-button" 
                    onclick="toggleReplyForm('${comment.id}')"
                    aria-label="Reply to ${escapeHtml(comment.user_name)}'s comment">
                <svg class="reply-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 17L4 12L9 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20 18V16C20 13.7909 18.2091 12 16 12H4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="reply-text">Reply</span>
            </button>
        </div>
        <div class="reply-form-container" id="reply-form-${comment.id}" style="display: none;">
            <form class="reply-form" onsubmit="submitReply(event, '${comment.id}')">
                <textarea class="reply-textarea" 
                          placeholder="Write a reply..." 
                          maxlength="1000" 
                          rows="3"
                          required></textarea>
                <div class="reply-form-actions">
                    <button type="button" class="reply-cancel-btn" onclick="hideReplyForm('${comment.id}')">Cancel</button>
                    <button type="submit" class="reply-submit-btn">Reply</button>
                </div>
            </form>
        </div>
        <div class="children-comments"></div>
    `;

    return commentContainer;
}

/**
 * Escape HTML to prevent XSS attacks
 * @param {string} text - The text to escape
 * @returns {string} The escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
