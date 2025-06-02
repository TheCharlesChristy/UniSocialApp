/**
 * Comment View Component JavaScript
 * Handles like/unlike functionality for comments
 */

// Initialize API handler only if not already defined
if (typeof window.commentViewAPI === 'undefined') {
    window.commentViewAPI = new APIHandler();
}

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializeCommentView();
    });
} else {
    initializeCommentView();
}

function initializeCommentView() {
    // Listen for both reply events to handle them appropriately
    document.addEventListener('replyAdded', handleReplyAdded);
    document.addEventListener('commentAdded', handlePossibleReply);
    
    // Add keyboard accessibility for comment like buttons
    const commentLikeButtons = document.querySelectorAll('.comment-like-button');
    commentLikeButtons.forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                button.click();
            }
        });
    });
    
    // Add keyboard accessibility for reply buttons
    const replyButtons = document.querySelectorAll('.comment-reply-button');
    replyButtons.forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                button.click();
            }
        });
    });
    
    // Add ESC key handler to close reply forms
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const visibleReplyForms = document.querySelectorAll('.reply-form-container[style*="block"]');
            visibleReplyForms.forEach(form => {
                const commentId = form.id.replace('reply-form-', '');
                hideReplyForm(commentId);
            });
        }
    });    // Listen for comment added events to handle reply comments
    document.addEventListener('replyAdded', handleReplyAdded);
}

/**
 * Handle the replyAdded event for reply comments
 * @param {CustomEvent} event - The replyAdded event
 */
function handleReplyAdded(event) {
    const { postId, parentCommentId, reply } = event.detail;
    
    if (!reply || !parentCommentId) {
        console.error('Missing required data for reply handling', event.detail);
        return;
    }

    console.log('Handling reply to comment:', parentCommentId, reply);

    // Add the reply comment to the appropriate parent comment
    addReplyToDOM(parentCommentId, reply);
}

/**
 * Add a new reply comment to the DOM under its parent comment
 * @param {string} parentCommentId - The ID of the parent comment
 * @param {object} reply - The reply comment data
 */
function addReplyToDOM(parentCommentId, reply) {
    console.log('Adding reply to DOM for parent:', parentCommentId, reply);
    
    // Find the parent comment's children container
    const parentComment = document.querySelector(`[data-comment-id="${parentCommentId}"]`);
    
    if (!parentComment) {
        console.error('Parent comment not found for reply:', parentCommentId);
        // Check if any element with this comment ID exists
        const anyMatch = document.querySelectorAll(`[data-comment-id]`);
        console.log('Available comment elements:', anyMatch.length);
        anyMatch.forEach(el => console.log('Comment ID:', el.getAttribute('data-comment-id')));
        return;
    }

    const childrenContainer = parentComment.querySelector('.children-comments');
    
    if (!childrenContainer) {
        console.error('Children comments container not found for parent:', parentCommentId);
        console.log('Parent comment structure:', parentComment.outerHTML);
        
        // Try to create a children container if it doesn't exist
        const newChildrenContainer = document.createElement('div');
        newChildrenContainer.className = 'children-comments';
        parentComment.appendChild(newChildrenContainer);
        
        // Use the newly created container
        const replyElement = createReplyElement(reply);
        if (replyElement) {
            newChildrenContainer.appendChild(replyElement);
            animateNewReply(replyElement);
        }
        return;
    }

    // Create reply element (replies have a slightly different structure)
    const replyElement = createReplyElement(reply);    if (replyElement) {
        // Add reply to the beginning of the children comments (most recent first)
        if (childrenContainer.firstChild) {
            childrenContainer.insertBefore(replyElement, childrenContainer.firstChild);
        } else {
            childrenContainer.appendChild(replyElement);
        }
        
        // Add animation
        animateNewReply(replyElement);
        
        // Update the UI to show there are replies
        updateReplyUIForParent(parentComment);
    }
}

/**
 * Create a reply comment DOM element from reply data
 * @param {object} reply - The reply comment data
 * @returns {Element|null} The created reply element
 */
function createReplyElement(reply) {
    if (!reply || !reply.id) {
        console.error('Invalid reply data provided');
        return null;
    }

    // Create the reply comment container (similar to comment but with reply styling)
    const replyContainer = document.createElement('div');
    replyContainer.className = 'comment-container reply-comment';
    replyContainer.setAttribute('role', 'article');
    replyContainer.setAttribute('aria-label', `Reply by ${reply.user_name}`);
    replyContainer.setAttribute('data-comment-id', reply.id);

    // Create reply HTML structure (simpler than main comments, no nested replies)
    replyContainer.innerHTML = `
        <div class="comment-header">
            <div class="comment-avatar">
                <img src="${reply.user_profile_picture_url || '../assets/images/default-profile.svg'}" 
                     alt="${reply.user_name}'s profile picture" 
                     class="profile-picture"
                     onerror="this.src='../assets/images/default-profile.svg'">
            </div>
            <div class="comment-user-info">
                <a href="profile.php?user_id=${reply.user_id}" class="username-link">
                    <span class="username">${escapeHtml(reply.user_name)}</span>
                </a>
                <span class="comment-time">Just now</span>
            </div>
        </div>
        <div class="comment-content">
            <p class="comment-text">${escapeHtml(reply.content)}</p>
        </div>
        <div class="comment-actions">
            <button class="comment-like-button" 
                    onclick="toggleCommentLike('${reply.id}')"
                    data-liked="0"
                    data-comment-id="${reply.id}"
                    aria-label="Like this reply">
                <svg class="like-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 14C19 14 17 16 12 21C7 16 5 14 5 14C3.5 12.5 3.5 9.5 5 8C6.5 6.5 9.5 6.5 11 8L12 9L13 8C14.5 6.5 17.5 6.5 19 8C20.5 9.5 20.5 12.5 19 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="like-count" id="comment-like-count-${reply.id}">0</span>
            </button>
        </div>
    `;

    return replyContainer;
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
 * Toggle like status for a comment
 * @param {string} commentId - The ID of the comment to like/unlike
 */
window.toggleCommentLike = async function(commentId) {
    if (!commentId) {
        console.error('Comment ID is required to toggle like');
        return;
    }

    const likeButton = document.querySelector(`.comment-like-button[data-comment-id="${commentId}"]`);
    const likeCountElement = document.getElementById(`comment-like-count-${commentId}`);
    
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
            // Unlike the comment
            endpoint = '/posts/unlike_comment';
            expectedNewCount = Math.max(0, currentCount - 1);
        } else {
            // Like the comment
            endpoint = '/posts/like_comment';
            expectedNewCount = currentCount + 1;
        }

        response = await window.commentViewAPI.authenticatedRequest(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ comment_id: parseInt(commentId) })
        });

        // Check if the API call was successful
        if (response && response.success) {
            // Update UI with server response count if available, otherwise use calculated count
            const serverCount = response.likes_count !== undefined ? response.likes_count : expectedNewCount;
            const newLikedState = !isCurrentlyLiked;
            
            window.updateCommentLikeUI(commentId, newLikedState, serverCount);
            
            // Show success feedback (optional)
            showCommentFeedback(likeButton, isCurrentlyLiked ? 'Unliked' : 'Liked', 'success');
        } else {
            throw new Error(response?.message || 'Failed to update like status');
        }

    } catch (error) {
        console.error('Error toggling comment like:', error);
        
        // Show error feedback
        showCommentFeedback(likeButton, 'Error: ' + error.message, 'error');
        
        // UI stays the same since we haven't updated it yet
    } finally {
        // Remove loading state
        likeButton.classList.remove('loading');
        likeButton.disabled = false;
    }
}

/**
 * Update the comment like button UI and count
 * @param {string} commentId - The comment ID
 * @param {boolean} isLiked - Whether the comment is now liked
 * @param {number} likeCount - The new like count
 */
window.updateCommentLikeUI = function(commentId, isLiked, likeCount) {
    const likeButton = document.querySelector(`.comment-like-button[data-comment-id="${commentId}"]`);
    const likeCountElement = document.getElementById(`comment-like-count-${commentId}`);
    
    if (!likeButton || !likeCountElement) {
        console.error('Like button or count element not found for update');
        return;
    }

    // Update like status
    likeButton.setAttribute('data-liked', isLiked ? '1' : '0');
    
    // Update aria-label for accessibility
    likeButton.setAttribute('aria-label', isLiked ? 'Unlike this comment' : 'Like this comment');
    
    // Update count
    likeCountElement.textContent = likeCount;

    // Add visual feedback animation
    likeCountElement.style.transform = 'scale(1.2)';
    setTimeout(() => {
        likeCountElement.style.transform = 'scale(1)';
    }, 150);
}

/**
 * Show temporary feedback message for comment actions
 * @param {Element} element - The element to show feedback near
 * @param {string} message - The feedback message
 * @param {string} type - The type of feedback ('success' or 'error')
 */
function showCommentFeedback(element, message, type = 'success') {
    // Remove any existing feedback
    const existingFeedback = element.querySelector('.comment-feedback-message');
    if (existingFeedback) {
        existingFeedback.remove();
    }

    // Create feedback element
    const feedback = document.createElement('span');
    feedback.className = `comment-feedback-message comment-feedback-${type}`;
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
 * Handle authentication errors for comments
 * @param {Error} error - The authentication error
 */
function handleCommentAuthError(error) {
    console.error('Authentication error in comment view:', error);
    
    // Redirect to login if not authenticated
    if (error.message.includes('authentication') || error.message.includes('token')) {
        window.location.href = 'login.php';
    }
}

/**
 * Toggle reply form visibility for a comment
 * @param {string} commentId - The ID of the comment to reply to
 */
window.toggleReplyForm = function(commentId) {
    const replyForm = document.getElementById(`reply-form-${commentId}`);
    const isVisible = replyForm.style.display !== 'none';
    
    if (isVisible) {
        hideReplyForm(commentId);
    } else {
        showReplyForm(commentId);
    }
}

/**
 * Show reply form for a comment
 * @param {string} commentId - The ID of the comment to reply to
 */
window.showReplyForm = function(commentId) {
    // Hide all other reply forms first
    const allReplyForms = document.querySelectorAll('.reply-form-container');
    allReplyForms.forEach(form => {
        form.style.display = 'none';
    });
    
    const replyForm = document.getElementById(`reply-form-${commentId}`);
    const textarea = replyForm.querySelector('.reply-textarea');
    
    replyForm.style.display = 'block';
    textarea.focus();
}

/**
 * Hide reply form for a comment
 * @param {string} commentId - The ID of the comment
 */
window.hideReplyForm = function(commentId) {
    const replyForm = document.getElementById(`reply-form-${commentId}`);
    const textarea = replyForm.querySelector('.reply-textarea');
    
    replyForm.style.display = 'none';
    textarea.value = ''; // Clear the textarea
}

/**
 * Submit a reply to a comment
 * @param {Event} event - The form submit event
 * @param {string} parentCommentId - The ID of the parent comment
 */
window.submitReply = async function(event, parentCommentId) {
    event.preventDefault();
    
    const form = event.target;
    const textarea = form.querySelector('.reply-textarea');
    const submitBtn = form.querySelector('.reply-submit-btn');
    const cancelBtn = form.querySelector('.reply-cancel-btn');    const content = textarea.value.trim();
    
    // Validate inputs
    if (!parentCommentId) {
        showCommentFeedback(submitBtn, 'Error: Invalid parent comment ID', 'error');
        return;
    }
    
    if (!content) {
        showCommentFeedback(submitBtn, 'Please enter a reply', 'error');
        textarea.focus();
        return;
    }
    
    // Add loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Posting...';
    cancelBtn.disabled = true;
      try {
        // Get the post ID from the current page URL or a data attribute
        const postId = getPostIdFromContext(form);
        
        if (!postId) {
            throw new Error('Unable to determine post ID');
        }
        
        const response = await window.commentViewAPI.authenticatedRequest('/posts/add_comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                post_id: parseInt(postId),
                content: content,
                parent_comment_id: parseInt(parentCommentId)
            })
        });        if (response && response.success) {
            // Show success message
            showGlobalFeedback('Reply posted successfully!', 'success');
            
            // Clear and hide the reply form
            textarea.value = '';
            hideReplyForm(parentCommentId);
              // Dispatch specific replyAdded event with reply data from server response
            const replyAddedEvent = new CustomEvent('replyAdded', {
                detail: { 
                    postId: postId,
                    parentCommentId: parentCommentId,
                    reply: response.comment ? {
                        id: response.comment.comment_id,
                        content: response.comment.content,
                        user_id: response.comment.user_id,
                        user_name: response.comment.first_name + ' ' + response.comment.last_name,
                        user_profile_picture_url: response.comment.profile_picture || '../assets/images/default-profile.svg',
                        parent_comment_id: response.comment.parent_comment_id,
                        created_at: response.comment.created_at,
                        likes_count: response.comment.likes_count,
                        has_liked: response.comment.user_has_liked
                    } : null
                }
            });
            document.dispatchEvent(replyAddedEvent);
            
        } else {
            throw new Error(response?.message || 'Failed to post reply');
        }
          } catch (error) {
        console.error('Error posting reply:', error);
        
        // Show appropriate error message
        let errorMessage = 'Error posting reply';
        if (error.message.includes('authentication') || error.message.includes('token')) {
            errorMessage = 'Please log in to post replies';
            // Redirect to login after a delay
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else if (error.message.includes('post_id')) {
            errorMessage = 'Unable to identify the post. Please refresh and try again.';
        } else {
            errorMessage = error.message;
        }
        
        showCommentFeedback(submitBtn, errorMessage, 'error');
    } finally {
        // Remove loading state
        submitBtn.disabled = false;
        submitBtn.textContent = 'Reply';
        cancelBtn.disabled = false;
    }
}

/**
 * Get the post ID from the current context (URL parameter or data attribute)
 * @param {Element} commentElement - Optional comment element to search from
 * @returns {string|null} The post ID
 */
function getPostIdFromContext(commentElement = null) {
    // If a comment element is provided, look for the post ID in its container
    if (commentElement) {
        const commentContainer = commentElement.closest('.comment-container[data-post-id]');
        if (commentContainer) {
            return commentContainer.getAttribute('data-post-id');
        }
    }
    
    // Try to get from URL parameter first
    const urlParams = new URLSearchParams(window.location.search);
    const postIdFromUrl = urlParams.get('post_id');
    
    if (postIdFromUrl) {
        return postIdFromUrl;
    }
    
    // Try to get from any comment container on the page
    const postContainer = document.querySelector('[data-post-id]');
    if (postContainer) {
        return postContainer.getAttribute('data-post-id');
    }
    
    // Try to get from any hidden input or meta tag
    const postIdInput = document.querySelector('input[name="post_id"]');
    if (postIdInput) {
        return postIdInput.value;
    }
    
    return null;
}

/**
 * Refresh the comments section
 * Attempts to reload only the comment section without full page refresh
 */
function refreshComments() {
    try {
        // First, check if we're on a valid page with a post ID
        const postId = getPostIdFromContext();
        if (!postId) {
            console.warn('Cannot refresh comments: Post ID not found. Will show success message only.');
            showGlobalFeedback('Reply posted successfully!', 'success');
            return;
        }
        
        // Instead of immediately refreshing the whole page, try to reload just the comments
        // Add a delay to ensure the server has processed the new comment
        setTimeout(() => {
            try {
                // Try to reload only the current page to avoid the filename error
                const currentUrl = window.location.href;
                
                // Check if the current URL is valid before attempting reload
                if (currentUrl && currentUrl.length > 0) {
                    // Use location.href instead of replace to ensure proper reload
                    window.location.href = currentUrl;
                } else {
                    console.error('Invalid current URL, cannot refresh');
                    showGlobalFeedback('Reply posted! Please refresh the page manually to see it.', 'success');
                }
            } catch (reloadError) {
                console.error('Error during page reload:', reloadError);
                showGlobalFeedback('Reply posted! Please refresh the page manually to see it.', 'success');
            }
        }, 1000); // Increased delay to ensure server processing
        
    } catch (error) {
        console.error('Error in refreshComments:', error);
        // If refresh fails, show a user-friendly message
        showGlobalFeedback('Reply posted successfully! Please refresh the page to see it.', 'success');
    }
}

/**
 * Show a global feedback message that's more prominent
 * @param {string} message - The feedback message
 * @param {string} type - The type of feedback ('success' or 'error')
 */
function showGlobalFeedback(message, type = 'success') {
    // Remove any existing global feedback
    const existingFeedback = document.querySelector('.global-feedback-message');
    if (existingFeedback) {
        existingFeedback.remove();
    }

    // Create feedback element
    const feedback = document.createElement('div');
    feedback.className = `global-feedback-message global-feedback-${type}`;
    feedback.textContent = message;
    feedback.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        max-width: 300px;
        word-wrap: break-word;
    `;

    // Append to body
    document.body.appendChild(feedback);

    // Animate in
    setTimeout(() => {
        feedback.style.opacity = '1';
        feedback.style.transform = 'translateX(0)';
    }, 10);

    // Remove after delay
    setTimeout(() => {
        feedback.style.opacity = '0';
        feedback.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (feedback.parentNode) {
                feedback.remove();
            }
        }, 300);
    }, 5000);
}

/**
 * Load more replies for a comment
 * @param {number} commentId - The ID of the parent comment
 * @param {number} postId - The ID of the post
 */
window.loadMoreReplies = async function(commentId, postId) {
    console.log('🔵 loadMoreReplies called with:', { commentId, postId });
    
    // Be more specific - target the load more button specifically, not the like button
    const button = document.querySelector(`.load-more-replies[data-comment-id="${commentId}"]`);
    console.log('🔵 Found load more button:', button);
    
    if (!button) {
        console.error('❌ Load more button not found for comment ID:', commentId);
        // Debug: show what buttons we do have for this comment
        const allButtons = document.querySelectorAll(`[data-comment-id="${commentId}"]`);
        console.log('🔵 All buttons with this comment ID:', allButtons);
        return;
    }
    
    // Add loading state
    console.log('🔵 Adding loading state to button');
    button.classList.add('loading');
    button.disabled = true;      try {
        // Find the parent comment and count existing replies in its children container
        const parentComment = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (!parentComment) {
            throw new Error('Parent comment not found');
        }
        
        const childrenContainer = parentComment.querySelector('.children-comments');
        if (!childrenContainer) {
            throw new Error('Children container not found');
        }
        
        // Count existing reply comments (exclude the load more button)
        const existingReplies = childrenContainer.querySelectorAll('.comment-container');
        const alreadyLoadedCount = existingReplies.length;
        console.log('🔵 Already loaded replies count:', alreadyLoadedCount);
        
        // Instead of using page numbers, use offset directly
        // We'll request replies starting from the number we already have
        const limit = 10;
        const offset = alreadyLoadedCount;
        
        console.log('🔵 Request info:', { alreadyLoadedCount, limit, offset });        // Since we have a mismatch between initial load (5 replies) and API pagination (10 per page),
        // we'll request from page 1 and filter out replies we already have
        const page = 1;
        
        // Use the API handler's authenticatedGet method
        console.log('🔵 Making API call to get_comment_replies with params:', {
            comment_id: commentId,
            page: page,
            limit: limit,
            note: 'Will filter client-side based on already loaded replies'
        });
        
        const apiResponse = await window.commentViewAPI.authenticatedGet('/posts/get_comment_replies', {
            comment_id: commentId,
            page: page,
            limit: limit
        });
        
        console.log('🔵 API Response received:', apiResponse);          if (apiResponse && apiResponse.success && apiResponse.replies && apiResponse.replies.length > 0) {
            console.log('✅ API call successful, total replies from API:', apiResponse.replies.length);
            
            // Filter out replies that are already displayed
            const existingReplyIds = Array.from(existingReplies).map(reply => 
                reply.getAttribute('data-comment-id')
            );
            console.log('🔵 Existing reply IDs:', existingReplyIds);
            
            const newReplies = apiResponse.replies.filter(reply => 
                !existingReplyIds.includes(reply.comment_id.toString())
            );
            console.log('🔵 New replies after filtering:', newReplies.length);
            
            if (newReplies.length > 0) {
                // Process and append new replies
                await appendNewReplies(commentId, newReplies, postId);
                console.log('✅ New replies appended');
                
                // Check if there are more replies to load
                const totalReplies = apiResponse.total_replies || 0;
                const totalLoadedAfterThis = alreadyLoadedCount + newReplies.length;
                const hasMoreReplies = totalLoadedAfterThis < totalReplies;
                
                console.log('🔵 Pagination info:', { 
                    totalReplies, 
                    totalLoadedAfterThis, 
                    hasMoreReplies 
                });
                
                if (!hasMoreReplies) {
                    // No more replies to load, hide the button
                    console.log('🔵 No more replies, removing button');
                    button.remove();
                } else {
                    // Update button text and reset loading state
                    console.log('🔵 More replies available, updating button');
                    button.querySelector('.button-text').textContent = "Load more replies";
                    button.classList.remove('loading');
                    button.disabled = false;
                }
            } else {
                // All replies were already loaded
                console.log('🔵 All replies were already displayed, removing button');
                button.remove();
            }
            
        } else {
            console.log('⚠️ API call returned no replies or failed:', apiResponse);
            // No more replies or error
            if (apiResponse && apiResponse.success && apiResponse.replies && apiResponse.replies.length === 0) {
                // No more replies, remove button
                console.log('🔵 No more replies available, removing button');
                button.remove();
            } else {
                throw new Error(apiResponse?.message || 'Failed to load more replies');
            }
        }
          } catch (error) {
        console.error('❌ Error in loadMoreReplies:', error);
        console.error('❌ Error stack:', error.stack);
        showGlobalFeedback('Error loading more replies: ' + error.message, 'error');
        
        // Reset loading state on error
        console.log('🔵 Resetting button loading state due to error');
        button.classList.remove('loading');
        button.disabled = false;
    }
}

/**
 * Append new replies to a comment's children container
 * @param {number} parentCommentId - The ID of the parent comment
 * @param {Array} replies - Array of reply objects
 * @param {number} postId - The ID of the post
 */
async function appendNewReplies(parentCommentId, replies, postId) {
    console.log('🟢 appendNewReplies called with:', { parentCommentId, repliesCount: replies.length, postId });
    
    // Try multiple ways to find the parent comment
    let parentComment = document.querySelector(`[data-comment-id="${parentCommentId}"]`);
    console.log('🟢 Found parent comment by data-comment-id:', parentComment);
    
    // If not found, try looking for the comment by its like button or reply button
    if (!parentComment) {
        console.log('🟢 Trying to find parent comment by like button');
        const likeButton = document.querySelector(`button[data-comment-id="${parentCommentId}"]`);
        console.log('🟢 Found like button:', likeButton);
        if (likeButton) {
            parentComment = likeButton.closest('.comment-container');
            console.log('🟢 Found parent comment via like button:', parentComment);
        }
    }
    
    // If still not found, try looking for the reply form
    if (!parentComment) {
        console.log('🟢 Trying to find parent comment by reply form');
        const replyForm = document.getElementById(`reply-form-${parentCommentId}`);
        console.log('🟢 Found reply form:', replyForm);
        if (replyForm) {
            parentComment = replyForm.closest('.comment-container');
            console.log('🟢 Found parent comment via reply form:', parentComment);
        }
    }
    
    if (!parentComment) {
        console.error('❌ Parent comment not found for comment ID:', parentCommentId);
        console.log('🟢 Available comment containers:', document.querySelectorAll('.comment-container'));
        console.log('🟢 Available elements with data-comment-id:', document.querySelectorAll('[data-comment-id]'));
        return;
    }
    
    const childrenContainer = parentComment.querySelector('.children-comments');
    console.log('🟢 Found children container:', childrenContainer);
    
    if (!childrenContainer) {
        console.error('❌ Children container not found for comment', parentCommentId);
        console.log('🟢 Parent comment structure:', parentComment.innerHTML);
        return;
    }
      // Find the load more button within this children container
    const loadMoreButton = childrenContainer.querySelector('.load-more-replies');
    console.log('🟢 Found load more button in children container:', loadMoreButton);
    
    for (const reply of replies) {
        console.log('🟢 Processing reply:', reply.comment_id);
        
        // Create HTML for each reply
        const replyHtml = await createReplyHTML(reply, postId);
        console.log('🟢 Generated reply HTML length:', replyHtml.length);
        
        // Insert before the load more button (if it exists), otherwise append to end
        if (loadMoreButton) {
            console.log('🟢 Inserting reply before load more button');
            loadMoreButton.insertAdjacentHTML('beforebegin', replyHtml);
        } else {
            console.log('🟢 Appending reply to end of children container');
            childrenContainer.insertAdjacentHTML('beforeend', replyHtml);
        }
    }
    
    console.log('🟢 All replies processed, reinitializing event handlers');
    // Reinitialize event handlers for the newly added comments
    reinitializeCommentHandlers(childrenContainer);
    console.log('✅ appendNewReplies completed successfully');
}

/**
 * Reinitialize event handlers for newly added comment elements
 * @param {Element} container - The container that holds the new comments
 */
function reinitializeCommentHandlers(container) {
    // Add keyboard accessibility for comment like buttons in the container
    const commentLikeButtons = container.querySelectorAll('.comment-like-button');
    commentLikeButtons.forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                button.click();
            }
        });
    });
    
    // Add keyboard accessibility for reply buttons in the container
    const replyButtons = container.querySelectorAll('.comment-reply-button');
    replyButtons.forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                button.click();
            }
        });
    });
}

/**
 * Create HTML for a single reply using the comment_view component
 * @param {Object} reply - Reply data object
 * @param {number} postId - The ID of the post
 * @returns {string} HTML string for the reply
 */
async function createReplyHTML(reply, postId) {
    try {
        // Use the profile picture URL as provided by the API (already formatted by formatMediaUrl)
        // If no profile picture is provided, use the default
        const profilePicture = reply.profile_picture || '../assets/images/default-profile.svg';
        
        // Create nested replies HTML if any
        let nestedRepliesHtml = '';
        if (reply.replies && reply.replies.length > 0) {
            for (const nestedReply of reply.replies) {
                nestedRepliesHtml += await createReplyHTML(nestedReply, postId);
            }
        }
        
        // Generate load more button for nested replies if needed
        let loadMoreButton = '';
        if (reply.has_more_replies && reply.reply_count > reply.loaded_replies) {
            loadMoreButton = `
            <button class="load-more-replies" 
                    onclick="loadMoreReplies(${reply.comment_id}, ${postId})"
                    data-comment-id="${reply.comment_id}"
                    data-post-id="${postId}"
                    data-page="1"
                    aria-label="Load more replies for this comment">
                <svg class="loading-spinner" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 2v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="button-text">Load more replies</span>
            </button>`;
        }
        
        // Build the comment HTML using the same structure as comment_view.html
        return `
        <div class="comment-container" role="article" aria-label="Comment by ${reply.username}" data-post-id="${postId}" data-comment-id="${reply.comment_id}">
            <div class="comment-header">
                <!-- Profile Picture -->
                <div class="comment-avatar">
                    <img src="${profilePicture}" 
                         alt="${reply.username}'s profile picture" 
                         class="profile-picture"
                         onerror="this.src='../assets/images/default-profile.svg'">
                </div>
                
                <!-- User Info -->
                <div class="comment-user-info">
                    <a href="profile.php?user_id=${reply.user_id}" class="username-link">
                        <span class="username">${reply.username}</span>
                    </a>
                </div>
            </div>
            
            <!-- Comment Content -->
            <div class="comment-content">
                <p class="comment-text">${reply.content}</p>
            </div>
            
            <!-- Comment Actions -->
            <div class="comment-actions">
                <button class="comment-like-button" 
                        onclick="toggleCommentLike('${reply.comment_id}')"
                        data-liked="${reply.user_has_liked || 0}"
                        data-comment-id="${reply.comment_id}"
                        aria-label="${reply.user_has_liked ? 'Unlike this comment' : 'Like this comment'}">
                    <svg class="like-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 14C19 14 17 16 12 21C7 16 5 14 5 14C3.5 12.5 3.5 9.5 5 8C6.5 6.5 9.5 6.5 11 8L12 9L13 8C14.5 6.5 17.5 6.5 19 8C20.5 9.5 20.5 12.5 19 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="like-count" id="comment-like-count-${reply.comment_id}">${reply.likes_count || 0}</span>
                </button>
                  
                <button class="comment-reply-button" 
                        onclick="toggleReplyForm('${reply.comment_id}')"
                        aria-label="Reply to ${reply.username}'s comment">
                    <svg class="reply-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 17L4 12L9 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20 18V16C20 13.7909 18.2091 12 16 12H4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="reply-text">Reply</span>
                </button>
            </div>
            
            <!-- Reply Form -->
            <div class="reply-form-container" id="reply-form-${reply.comment_id}" style="display: none;">
                <form class="reply-form" onsubmit="submitReply(event, '${reply.comment_id}')">
                    <textarea class="reply-textarea" 
                              placeholder="Write a reply..." 
                              maxlength="1000" 
                              rows="3"
                              required></textarea>
                    <div class="reply-form-actions">
                        <button type="button" class="reply-cancel-btn" onclick="hideReplyForm('${reply.comment_id}')">Cancel</button>
                        <button type="submit" class="reply-submit-btn">Reply</button>
                    </div>
                </form>
            </div>
            
            <!-- Children Comments -->
            <div class="children-comments">
                ${nestedRepliesHtml}
                ${loadMoreButton}
            </div>
        </div>`;
    } catch (error) {
        console.error('Error creating reply HTML:', error);
        // Fallback to a simple HTML structure
        return `
        <div class="comment-container error-comment">
            <p>Error loading comment. Please refresh the page.</p>
        </div>`;
    }
}

/**
 * Animate a new reply element when it's added to the DOM
 * @param {Element} element - The reply element to animate
 */
function animateNewReply(element) {
    if (!element) return;
    
    // Add smooth entrance animation
    element.style.opacity = '0';
    element.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
        element.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        element.style.opacity = '1';
        element.style.transform = 'translateY(0)';
    }, 10);
}

/**
 * Update the UI of a parent comment to show it has replies
 * @param {Element} parentComment - The parent comment element
 */
function updateReplyUIForParent(parentComment) {
    if (!parentComment) return;
    
    // Add a class to indicate this comment has active replies
    parentComment.classList.add('has-replies');
    
    // Update reply count if available
    const replyCountElement = parentComment.querySelector('.reply-count');
    if (replyCountElement) {
        const currentCount = parseInt(replyCountElement.textContent) || 0;
        replyCountElement.textContent = currentCount + 1;
        
        // Add visual feedback animation
        replyCountElement.style.transform = 'scale(1.2)';
        setTimeout(() => {
            replyCountElement.style.transform = 'scale(1)';
        }, 150);
    }
    
    // Make sure the children container is visible
    const childrenContainer = parentComment.querySelector('.children-comments');
    if (childrenContainer) {
        childrenContainer.style.display = 'block';
    }
}

/**
 * Handle a commentAdded event that might actually be a reply
 * This provides backward compatibility with code that might not be using the new replyAdded event
 * @param {CustomEvent} event - The commentAdded event
 */
function handlePossibleReply(event) {
    const { postId, comment } = event.detail;
    
    // Only process if this is actually a reply comment (has parent_comment_id)
    if (!comment || !comment.parent_comment_id) {
        return; // Not a reply, ignore it
    }
    
    console.log('Caught a reply through commentAdded event, handling it properly', comment);
    
    // Process this as a reply
    addReplyToDOM(comment.parent_comment_id, comment);
}

/**
 * Debug function to log the current state of comment elements in the DOM
 * Can be called from console to diagnose issues
 */
window.debugCommentSystem = function() {
    console.group('Comment System Debug Information');
    
    // Check for all comments in the DOM
    const allComments = document.querySelectorAll('[data-comment-id]');
    console.log('Total comment elements found:', allComments.length);
    
    // Map of comment IDs to their elements
    const commentMap = {};
    allComments.forEach(comment => {
        const id = comment.getAttribute('data-comment-id');
        commentMap[id] = comment;
        console.log(`Comment ID ${id}:`, {
            hasChildrenContainer: !!comment.querySelector('.children-comments'),
            isReply: comment.classList.contains('reply-comment'),
            parentElement: comment.parentElement.className
        });
    });
    
    // Check for posts with comment lists
    const commentLists = document.querySelectorAll('.comments-list');
    console.log('Comment list containers found:', commentLists.length);
    commentLists.forEach(list => {
        console.log(`Comments list for post ${list.getAttribute('data-post-id')}:`, {
            childComments: list.querySelectorAll('[data-comment-id]').length,
            isVisible: window.getComputedStyle(list).display !== 'none'
        });
    });
    
    // Check for reply forms
    const replyForms = document.querySelectorAll('.reply-form-container');
    console.log('Reply forms found:', replyForms.length);
    replyForms.forEach(form => {
        const commentId = form.id.replace('reply-form-', '');
        console.log(`Reply form for comment ${commentId}:`, {
            isVisible: window.getComputedStyle(form).display !== 'none',
            hasTextarea: !!form.querySelector('textarea'),
            associatedComment: commentMap[commentId] ? 'Found' : 'Not found'
        });
    });
    
    console.groupEnd();
    
    return {
        commentCount: allComments.length,
        commentListCount: commentLists.length,
        replyFormCount: replyForms.length
    };
}

// Set up debug event listeners when in development mode
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    document.addEventListener('commentAdded', function(e) {
        console.groupCollapsed('✅ commentAdded event detected');
        console.log('Event:', e);
        console.log('Post ID:', e.detail.postId);
        console.log('Comment:', e.detail.comment);
        if (e.detail.comment && e.detail.comment.parent_comment_id) {
            console.warn('⚠️ This appears to be a reply comment but is using commentAdded event!');
        }
        console.groupEnd();
    });
    
    document.addEventListener('replyAdded', function(e) {
        console.groupCollapsed('🔄 replyAdded event detected');
        console.log('Event:', e);
        console.log('Post ID:', e.detail.postId);
        console.log('Parent Comment ID:', e.detail.parentCommentId);
        console.log('Reply:', e.detail.reply);
        console.groupEnd();
    });
}
