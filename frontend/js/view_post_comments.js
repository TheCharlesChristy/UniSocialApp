/* SocialConnect View Post Comments JavaScript */
/* Handles navigation to comments page and accessibility */

// Initialize component functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeViewPostComments();
});

function initializeViewPostComments() {
    // Add keyboard support for comment button clicks
    const commentButtons = document.querySelectorAll('.view-comments-button');
    
    commentButtons.forEach(button => {
        // Handle keyboard navigation
        button.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                event.stopPropagation();
                
                // Extract post_id from the onclick attribute
                const onclickAttr = this.getAttribute('onclick');
                if (onclickAttr) {
                    // Parse the post_id from onclick="navigateToComments('post_id')"
                    const match = onclickAttr.match(/navigateToComments\('([^']+)'\)/);
                    if (match && match[1]) {
                        navigateToComments(match[1]);
                    }
                }
            }
        });
    });
}

/**
 * Navigate to comments page for a specific post
 * @param {string} postId - The ID of the post to view comments for
 */
window.navigateToComments = function(postId) {
    if (!postId) {
        console.error('Post ID is required to navigate to comments');
        return;
    }
    
    // Redirect to comments page with post ID
    window.location.href = `comments.php?post_id=${postId}`;
}
