/**
 * Post Stats and Caption Component JavaScript
 * Handles hide/show comments functionality for individual posts
 */

// Initialize component functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializePostStatsAndCaption();
});

function initializePostStatsAndCaption() {
    // Initialize all post stats and caption containers
    const containers = document.querySelectorAll('.post-stats-and-caption-container');
    
    containers.forEach(container => {
        new PostStatsAndCaption(container);
    });
}

class PostStatsAndCaption {
    constructor(container) {
        this.container = container;
        this.hideButton = null;
        this.commentsSection = null;
        this.isCommentsHidden = false;
        
        this.init();
    }
    
    init() {
        this.findElements();
        this.bindEvents();
    }
    
    findElements() {
        // Find the hide button and comments section in this container
        this.hideButton = this.container.querySelector('.hide-comments-btn');
        this.commentsSection = this.container.querySelector('.third-content');
    }
    
    bindEvents() {
        if (this.hideButton) {
            this.hideButton.addEventListener('click', (e) => this.toggleComments(e));
            
            // Handle keyboard navigation
            this.hideButton.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggleComments(e);
                }
            });
        }
    }
      toggleComments(event) {
        event.preventDefault();
        event.stopPropagation();
        
        if (!this.commentsSection) {
            console.warn('Comments section not found for this post');
            return;
        }
        
        this.isCommentsHidden = !this.isCommentsHidden;
        
        if (this.isCommentsHidden) {
            this.hideComments();
        } else {
            this.showComments();
        }
        
        // Find the post ID associated with this comments section
        let postId = null;
        const commentsList = this.commentsSection.querySelector('.comments-list');
        if (commentsList) {
            postId = commentsList.getAttribute('data-post-id');
        }
        
        if (!postId) {
            // Try to get from the stored attribute
            postId = this.commentsSection.getAttribute('data-stored-post-id');
        }
        
        // Dispatch an event to let other components know comments visibility changed
        if (postId) {
            const event = new CustomEvent('commentsVisibilityChanged', { 
                detail: { 
                    postId: postId,
                    isHidden: this.isCommentsHidden
                }
            });
            document.dispatchEvent(event);
        }
    }
      hideComments() {
        if (!this.commentsSection) return;
        
        // Hide the comments section with animation - keep DOM accessible
        this.commentsSection.style.transition = 'all 0.3s ease';
        this.commentsSection.style.opacity = '0';
        this.commentsSection.style.maxHeight = '0';
        this.commentsSection.style.overflow = 'hidden';
        this.commentsSection.style.paddingTop = '0';
        this.commentsSection.style.paddingBottom = '0';
        this.commentsSection.style.position = 'absolute'; // Position off-screen instead of removing from flow
        this.commentsSection.style.visibility = 'hidden'; // Hide visually but keep accessible to DOM
        
        // Store the data-post-id for future reference
        const commentsList = this.commentsSection.querySelector('.comments-list');
        if (commentsList) {
            this.commentsSection.setAttribute('data-stored-post-id', commentsList.getAttribute('data-post-id'));
        }
        
        // Update button state
        this.updateButtonState(true);
        
        // Update accessibility but keep it in DOM for JavaScript operations
        this.commentsSection.setAttribute('aria-hidden', 'true');
        this.hideButton.setAttribute('aria-expanded', 'false');
        this.hideButton.setAttribute('aria-label', 'Show comments section');
        
        // After animation, fully hide
        setTimeout(() => {
            if (this.isCommentsHidden) {
                this.commentsSection.style.display = 'none';
            }
        }, 300);
    }
      showComments() {
        if (!this.commentsSection) return;
        
        // Show the comments section with animation
        this.commentsSection.style.display = 'block';
        this.commentsSection.style.transition = 'all 0.3s ease';
        this.commentsSection.style.position = ''; // Reset to default
        this.commentsSection.style.visibility = ''; // Reset to default
        
        // Force reflow
        this.commentsSection.offsetHeight;
        
        this.commentsSection.style.opacity = '1';
        this.commentsSection.style.maxHeight = 'none';
        this.commentsSection.style.overflow = 'visible';
        this.commentsSection.style.paddingTop = 'var(--spacing-sm)';
        this.commentsSection.style.paddingBottom = 'var(--spacing-sm)';
        
        // Update button state
        this.updateButtonState(false);
        
        // Update accessibility
        this.commentsSection.setAttribute('aria-hidden', 'false');
        this.hideButton.setAttribute('aria-expanded', 'true');
        this.hideButton.setAttribute('aria-label', 'Hide comments section');
    }
      updateButtonState(isHidden) {
        if (!this.hideButton) return;
        
        const hideText = this.hideButton.querySelector('.hide-text');
        const hideIcon = this.hideButton.querySelector('.hide-icon');
        
        if (isHidden) {
            hideText.textContent = 'Show';
            this.hideButton.classList.add('comments-hidden');
            // Rotate icon to indicate expand (point down)
            if (hideIcon) {
                hideIcon.style.transform = 'rotate(180deg)';
            }
        } else {
            hideText.textContent = 'Hide';
            this.hideButton.classList.remove('comments-hidden');
            // Reset icon rotation (point up)
            if (hideIcon) {
                hideIcon.style.transform = 'rotate(0deg)';
            }
        }
    }
}

// Export for global access if needed
if (typeof window !== 'undefined') {
    window.PostStatsAndCaption = PostStatsAndCaption;
}
