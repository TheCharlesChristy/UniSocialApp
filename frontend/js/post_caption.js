/* SocialConnect Post Caption JavaScript */
/* Handles username click navigation and accessibility */

// Initialize component functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializePostCaption();
});

function initializePostCaption() {
    // Add keyboard support for username clicks
    const usernames = document.querySelectorAll('.username');
    
    usernames.forEach(username => {
        // Handle keyboard navigation
        username.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                event.stopPropagation();
                
                // Extract username from the onclick attribute or text content
                const onclickAttr = this.getAttribute('onclick');
                if (onclickAttr) {
                    // Parse the username from onclick="gotoProfile('username')"
                    const match = onclickAttr.match(/gotoProfile\('([^']+)'\)/);
                    if (match && match[1]) {
                        gotoProfile(match[1]);
                    }
                } else {
                    // Fallback to text content if no onclick
                    const usernameText = this.textContent.trim();
                    if (usernameText) {
                        gotoProfile(usernameText);
                    }
                }
            }
        });
    });
}

function gotoProfile(username) {
    try {
        // Check if user is authenticated (using userAPI from generic-api.js)
        if (typeof userAPI !== 'undefined' && !userAPI.isAuthenticated()) {
            console.warn('User not authenticated, redirecting to login');
            window.location.href = '../pages/login.php';
            return;
        }

        console.log('Navigating to profile for user:', username);
        if (!username) {
            console.error('Username not found');
            if (typeof APIUtils !== 'undefined') {
                APIUtils.showError('Username not found');
            }
            return;
        }

        // Navigate to user profile page
        window.location.href = '../pages/view-account.php?username=' + encodeURIComponent(username);
    } catch (error) {
        console.error('Error navigating to profile:', error);
        
        // Show error message if APIUtils is available
        if (typeof APIUtils !== 'undefined') {
            APIUtils.showError('Unable to access profile page');
        } else {
            alert('Unable to access profile page');
        }
    }
}

// Export function for global access if needed
if (typeof window !== 'undefined') {
    window.gotoProfile = gotoProfile;
    window.initializePostCaption = initializePostCaption;
}
