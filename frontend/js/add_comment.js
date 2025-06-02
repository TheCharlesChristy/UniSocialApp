/**
 * Add Comment Component JavaScript
 * Handles comment form display, validation, and submission
 */

// Initialize API handler only if not already defined
if (typeof window.addCommentAPI === 'undefined') {
    window.addCommentAPI = new APIHandler();
}

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializeAddComment();
    });
} else {
    initializeAddComment();
}

function initializeAddComment() {
    // Add keyboard accessibility for comment buttons
    const addCommentButtons = document.querySelectorAll('.add-comment-btn');
    addCommentButtons.forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                button.click();
            }
        });
    });
    
    // Add ESC key handler to close comment forms
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const visibleCommentForms = document.querySelectorAll('.add-comment-form-container[style*="block"]');
            visibleCommentForms.forEach(form => {
                const postId = form.id.replace('addCommentForm-', '');
                hideCommentForm(postId);
            });
        }
    });

    // Initialize character counters for existing forms
    const textareas = document.querySelectorAll('.comment-textarea');
    textareas.forEach(textarea => {
        const postId = textarea.id.replace('commentTextarea-', '');
        initializeCharacterCounter(postId);
    });
}

/**
 * Show the comment form for a specific post
 * @param {string} postId - The ID of the post
 */
window.showCommentForm = function(postId) {
    if (!postId) {
        console.error('Post ID is required to show comment form');
        return;
    }

    const buttonContainer = document.getElementById(`addCommentButton-${postId}`);
    const formContainer = document.getElementById(`addCommentForm-${postId}`);
    const textarea = document.getElementById(`commentTextarea-${postId}`);

    if (!buttonContainer || !formContainer || !textarea) {
        console.error('Comment form elements not found for post:', postId);
        return;
    }

    // Hide button and show form with smooth transition
    buttonContainer.style.display = 'none';
    formContainer.style.display = 'block';
    formContainer.setAttribute('aria-hidden', 'false');

    // Focus on textarea for better UX
    setTimeout(() => {
        textarea.focus();
    }, 100);

    // Initialize character counter if not already done
    initializeCharacterCounter(postId);

    // Hide any existing messages
    hideMessage(postId);
};

/**
 * Hide the comment form for a specific post
 * @param {string} postId - The ID of the post
 */
window.hideCommentForm = function(postId) {
    if (!postId) {
        console.error('Post ID is required to hide comment form');
        return;
    }

    const buttonContainer = document.getElementById(`addCommentButton-${postId}`);
    const formContainer = document.getElementById(`addCommentForm-${postId}`);
    const textarea = document.getElementById(`commentTextarea-${postId}`);

    if (!buttonContainer || !formContainer || !textarea) {
        console.error('Comment form elements not found for post:', postId);
        return;
    }

    // Clear textarea content
    textarea.value = '';
    updateCharacterCount(postId);

    // Show button and hide form
    formContainer.style.display = 'none';
    formContainer.setAttribute('aria-hidden', 'true');
    buttonContainer.style.display = 'block';

    // Hide any messages
    hideMessage(postId);
};

/**
 * Initialize character counter for a comment form
 * @param {string} postId - The ID of the post
 */
function initializeCharacterCounter(postId) {
    const textarea = document.getElementById(`commentTextarea-${postId}`);
    const counterElement = document.getElementById(`characterCount-${postId}`);

    if (!textarea || !counterElement) {
        return;
    }

    // Remove existing event listeners to prevent duplicates
    textarea.removeEventListener('input', textarea._characterCountHandler);
    textarea.removeEventListener('paste', textarea._characterCountHandler);

    // Create new handler
    textarea._characterCountHandler = () => updateCharacterCount(postId);

    // Add event listeners
    textarea.addEventListener('input', textarea._characterCountHandler);
    textarea.addEventListener('paste', textarea._characterCountHandler);

    // Initialize count
    updateCharacterCount(postId);
}

/**
 * Update character count display
 * @param {string} postId - The ID of the post
 */
function updateCharacterCount(postId) {
    const textarea = document.getElementById(`commentTextarea-${postId}`);
    const counterElement = document.getElementById(`characterCount-${postId}`);
    const submitButton = document.getElementById(`submitBtn-${postId}`);

    if (!textarea || !counterElement) {
        return;
    }

    const currentLength = textarea.value.length;
    const maxLength = 1000;
    
    counterElement.textContent = currentLength;

    // Update styling based on character count
    const counterContainer = counterElement.parentElement;
    counterContainer.classList.remove('warning', 'error');

    if (currentLength > maxLength * 0.9) {
        counterContainer.classList.add('warning');
    }
    if (currentLength >= maxLength) {
        counterContainer.classList.add('error');
    }

    // Enable/disable submit button based on content
    if (submitButton) {
        const hasContent = textarea.value.trim().length > 0;
        const isUnderLimit = currentLength <= maxLength;
        submitButton.disabled = !hasContent || !isUnderLimit;
    }
}

/**
 * Submit a comment
 * @param {Event} event - The form submit event
 * @param {string} postId - The ID of the post
 */
window.submitComment = async function(event, postId) {
    event.preventDefault();

    if (!postId) {
        console.error('Post ID is required to submit comment');
        return;
    }

    const textarea = document.getElementById(`commentTextarea-${postId}`);
    const submitButton = document.getElementById(`submitBtn-${postId}`);

    if (!textarea || !submitButton) {
        console.error('Comment form elements not found for post:', postId);
        return;
    }

    const content = textarea.value.trim();

    // Validate content
    if (!content) {
        showMessage(postId, 'Please enter a comment.', 'error');
        textarea.focus();
        return;
    }

    if (content.length > 1000) {
        showMessage(postId, 'Comment is too long. Maximum 1000 characters allowed.', 'error');
        textarea.focus();
        return;
    }

    // Check authentication
    if (!window.addCommentAPI.isAuthenticated()) {
        showMessage(postId, 'Please log in to comment.', 'error');
        return;
    }

    // Set loading state
    setSubmitLoading(postId, true);

    try {
        const response = await window.addCommentAPI.authenticatedRequest('/posts/add_comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                post_id: parseInt(postId),
                content: content
            })
        });        if (response.success) {
            showMessage(postId, 'Comment added successfully!', 'success');
            
            // Clear the form after successful submission
            textarea.value = '';
            updateCharacterCount(postId);
            
            // Optionally hide the form after a delay
            setTimeout(() => {
                hideCommentForm(postId);
                
                // Trigger a custom event that other components can listen to
                // for refreshing comment lists
                const commentAddedEvent = new CustomEvent('commentAdded', {
                    detail: { 
                        postId: postId, 
                        comment: response.comment ? {
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
                document.dispatchEvent(commentAddedEvent);
            }, 1500);
        } else {
            showMessage(postId, response.message || 'Failed to add comment.', 'error');
        }
    } catch (error) {
        console.error('Error submitting comment:', error);
        showMessage(postId, error.message || 'Failed to add comment. Please try again.', 'error');
    } finally {
        setSubmitLoading(postId, false);
    }
};

/**
 * Set loading state for submit button
 * @param {string} postId - The ID of the post
 * @param {boolean} isLoading - Whether to show loading state
 */
function setSubmitLoading(postId, isLoading) {
    const submitButton = document.getElementById(`submitBtn-${postId}`);
    if (!submitButton) return;

    const submitText = submitButton.querySelector('.submit-text');
    const submitSpinner = submitButton.querySelector('.submit-spinner');

    if (isLoading) {
        submitButton.disabled = true;
        submitButton.classList.add('loading');
        if (submitText) submitText.style.display = 'none';
        if (submitSpinner) submitSpinner.style.display = 'block';
    } else {
        submitButton.disabled = false;
        submitButton.classList.remove('loading');
        if (submitText) submitText.style.display = 'block';
        if (submitSpinner) submitSpinner.style.display = 'none';
    }
}

/**
 * Show a message (success or error)
 * @param {string} postId - The ID of the post
 * @param {string} message - The message to display
 * @param {string} type - The type of message ('success' or 'error')
 */
function showMessage(postId, message, type = 'error') {
    const messageContainer = document.getElementById(`messageContainer-${postId}`);
    const messageElement = document.getElementById(`message-${postId}`);

    if (!messageContainer || !messageElement) {
        // Fallback to console and global notification
        console.log(`${type.toUpperCase()}: ${message}`);
        if (window.APIUtils) {
            if (type === 'success') {
                window.APIUtils.showSuccess(message);
            } else {
                window.APIUtils.showError(message);
            }
        }
        return;
    }

    messageElement.textContent = message;
    messageElement.className = `message ${type}`;
    messageContainer.style.display = 'block';

    // Auto-hide after delay
    const delay = type === 'success' ? 3000 : 5000;
    setTimeout(() => {
        hideMessage(postId);
    }, delay);
}

/**
 * Hide the message display
 * @param {string} postId - The ID of the post
 */
function hideMessage(postId) {
    const messageContainer = document.getElementById(`messageContainer-${postId}`);
    if (messageContainer) {
        messageContainer.style.display = 'none';
    }
}

/**
 * Utility function to validate comment content
 * @param {string} content - The comment content to validate
 * @returns {object} Validation result with isValid and message properties
 */
function validateComment(content) {
    if (!content || content.trim().length === 0) {
        return {
            isValid: false,
            message: 'Please enter a comment.'
        };
    }

    if (content.length > 1000) {
        return {
            isValid: false,
            message: 'Comment is too long. Maximum 1000 characters allowed.'
        };
    }

    // Check for basic content quality (optional)
    if (content.trim().length < 2) {
        return {
            isValid: false,
            message: 'Comment is too short.'
        };
    }

    return {
        isValid: true,
        message: ''
    };
}

// Export functions for testing or external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showCommentForm: window.showCommentForm,
        hideCommentForm: window.hideCommentForm,
        submitComment: window.submitComment,
        validateComment
    };
}
