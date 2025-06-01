// filepath: c:\xampp\htdocs\webdev\frontend\js\logged-in-header.js
/* SocialConnect Logged-in Header JavaScript */
/* Handles mobile navigation, user dropdown, notifications, and user data */

// Initialize header functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const header = new LoggedInHeader();
    header.init();
});

class LoggedInHeader {
    constructor() {
        // DOM elements
        this.mobileNavToggle = document.querySelector('.mobile-nav-toggle');
        this.mobileNavMenu = document.getElementById('mobile-menu');
        this.userProfileBtn = document.getElementById('userProfileBtn');
        this.userDropdownMenu = document.getElementById('userDropdownMenu');
        this.notificationBtn = document.getElementById('notificationBtn');
        this.notificationBadge = document.getElementById('notificationBadge');
        this.logoutBtns = document.querySelectorAll('.logout-btn');
        
        // API instances from generic-api.js
        this.userAPI = new UserAPI();
        this.api = new APIHandler();
        
        // State
        this.isDropdownOpen = false;
        this.isMobileMenuOpen = false;
        this.notificationCount = 0;
        this.currentUser = null;
        
        // Bind methods
        this.handleMobileNavToggle = this.handleMobileNavToggle.bind(this);
        this.handleUserProfileToggle = this.handleUserProfileToggle.bind(this);
        this.handleLogout = this.handleLogout.bind(this);
        this.handleNotificationClick = this.handleNotificationClick.bind(this);
        this.handleOutsideClick = this.handleOutsideClick.bind(this);
        this.handleEscapeKey = this.handleEscapeKey.bind(this);
    }

    async init() {
        try {
            // Check if user is authenticated
            if (!this.userAPI.isAuthenticated()) {
                console.warn('No authentication token found, redirecting to login');
                window.location.href = 'login.php';
                return;
            }

            // Set up event listeners
            this.setupEventListeners();
            
            // Load user data and notifications
            await this.loadUserData();
            await this.loadNotificationCount();
            
            // Start periodic notification updates
            this.startNotificationPolling();
            
            console.log('Logged-in header initialized successfully');
        } catch (error) {
            console.error('Failed to initialize header:', error);
            this.showError('Failed to load header data');
        }
    }

    setupEventListeners() {
        // Mobile navigation toggle
        if (this.mobileNavToggle) {
            this.mobileNavToggle.addEventListener('click', this.handleMobileNavToggle);
        }

        // User profile dropdown toggle
        if (this.userProfileBtn) {
            this.userProfileBtn.addEventListener('click', this.handleUserProfileToggle);
        }

        // Logout buttons
        this.logoutBtns.forEach(btn => {
            btn.addEventListener('click', this.handleLogout);
        });

        // Notification button
        if (this.notificationBtn) {
            this.notificationBtn.addEventListener('click', this.handleNotificationClick);
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', this.handleOutsideClick);
        
        // Close dropdowns with Escape key
        document.addEventListener('keydown', this.handleEscapeKey);

        // Close mobile menu when window is resized to desktop size
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768 && this.isMobileMenuOpen) {
                this.closeMobileMenu();
            }
        });
    }

    async loadUserData() {
        try {
            const userData = await this.userAPI.getCurrentUser();
            if (userData.success && userData.user) {
                this.currentUser = userData.user;
                this.updateUserDisplay(userData.user);
            } else {
                throw new Error('Invalid user data received');
            }
        } catch (error) {
            console.error('Failed to load user data:', error);
            
            // If authentication fails, redirect to login
            if (error.message.includes('Unauthorized') || error.message.includes('401')) {
                this.userAPI.removeAuthToken();
                window.location.href = 'login.php';
                return;
            }
            
            this.showError('Failed to load user profile');
        }
    }

    updateUserDisplay(user) {
        // Update profile picture
        const profilePicture = document.querySelector('.profile-picture');
        if (profilePicture && user.profile_picture) {
            // Handle both absolute and relative paths
            let profilePicSrc = user.profile_picture;
            if (!profilePicSrc.startsWith('http') && !profilePicSrc.startsWith('/')) {
                profilePicSrc = '../backend/' + user.profile_picture;
            }
            profilePicture.src = profilePicSrc;
            profilePicture.alt = `${user.first_name} ${user.last_name}'s profile picture`;
        }

        // Update user name
        const userNameElement = document.querySelector('.user-name');
        if (userNameElement) {
            userNameElement.textContent = `${user.first_name} ${user.last_name}`;
        }

        // Update any template placeholders that might still exist
        document.querySelectorAll('[data-user-name]').forEach(element => {
            element.textContent = `${user.first_name} ${user.last_name}`;
        });
    }

    async loadNotificationCount() {
        try {
            const response = await this.api.authenticatedRequest('/notifications/unread_count');
            if (response.success) {
                this.updateNotificationCount(response.count || 0);
            } else {
                console.warn('Failed to load notification count:', response.message);
            }
        } catch (error) {
            console.error('Error loading notification count:', error);
            // Don't show error to user for notifications, just log it
        }
    }

    updateNotificationCount(count) {
        this.notificationCount = count;
        
        if (this.notificationBadge) {
            if (count > 0) {
                this.notificationBadge.textContent = count > 99 ? '99+' : count.toString();
                this.notificationBadge.style.display = 'inline-block';
                this.notificationBadge.setAttribute('aria-label', `${count} unread notifications`);
            } else {
                this.notificationBadge.style.display = 'none';
                this.notificationBadge.setAttribute('aria-label', 'No unread notifications');
            }
        }

        // Update notification button accessibility
        if (this.notificationBtn) {
            this.notificationBtn.setAttribute('aria-label', 
                count > 0 ? `View ${count} notifications` : 'View notifications'
            );
        }
    }

    startNotificationPolling() {
        // Poll for notification count every 30 seconds
        this.notificationInterval = setInterval(async () => {
            await this.loadNotificationCount();
        }, 30000);
    }

    stopNotificationPolling() {
        if (this.notificationInterval) {
            clearInterval(this.notificationInterval);
            this.notificationInterval = null;
        }
    }

    handleMobileNavToggle(event) {
        event.preventDefault();
        event.stopPropagation();
        
        if (this.isMobileMenuOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    }    openMobileMenu() {
        if (!this.mobileNavMenu || !this.mobileNavToggle) return;
        
        this.isMobileMenuOpen = true;
        this.mobileNavMenu.setAttribute('aria-hidden', 'false');
        this.mobileNavToggle.setAttribute('aria-expanded', 'true');
        this.mobileNavMenu.classList.add('show');
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }    closeMobileMenu() {
        if (!this.mobileNavMenu || !this.mobileNavToggle) return;
        
        this.isMobileMenuOpen = false;
        this.mobileNavMenu.setAttribute('aria-hidden', 'true');
        this.mobileNavToggle.setAttribute('aria-expanded', 'false');
        this.mobileNavMenu.classList.remove('show');
        
        // Restore body scroll
        document.body.style.overflow = '';
    }

    handleUserProfileToggle(event) {
        event.preventDefault();
        event.stopPropagation();
        
        if (this.isDropdownOpen) {
            this.closeUserDropdown();
        } else {
            this.openUserDropdown();
        }
    }    openUserDropdown() {
        if (!this.userDropdownMenu || !this.userProfileBtn) return;
        
        this.isDropdownOpen = true;
        this.userProfileBtn.setAttribute('aria-expanded', 'true');
        this.userDropdownMenu.classList.add('show');
        
        // Focus first menu item for accessibility
        const firstMenuItem = this.userDropdownMenu.querySelector('a, button');
        if (firstMenuItem) {
            setTimeout(() => firstMenuItem.focus(), 10);
        }
    }    closeUserDropdown() {
        if (!this.userDropdownMenu || !this.userProfileBtn) return;
        
        this.isDropdownOpen = false;
        this.userProfileBtn.setAttribute('aria-expanded', 'false');
        this.userDropdownMenu.classList.remove('show');
    }

    handleNotificationClick(event) {
        event.preventDefault();
        
        // Navigate to a notifications page (you may need to create this)
        // For now, we'll just log the click
        console.log('Notification button clicked');
        
        // You can implement navigation to a notifications page here
        // window.location.href = 'notifications.php';
        
        // Or show a simple notification count message
        if (this.notificationCount > 0) {
            this.showSuccess(`You have ${this.notificationCount} unread notifications`);
        } else {
            this.showSuccess('No new notifications');
        }
    }

    async handleLogout(event) {
        event.preventDefault();
        
        const logoutBtn = event.target.closest('.logout-btn');
        if (!logoutBtn) return;
        
        try {
            // Show loading state
            const originalText = logoutBtn.textContent;
            logoutBtn.textContent = 'Logging out...';
            logoutBtn.disabled = true;
            
            // Stop notification polling
            this.stopNotificationPolling();
            
            // Call logout API
            await this.userAPI.logout();
            
            // Show success message briefly
            this.showSuccess('Logged out successfully');
            
            // Redirect to login page after a short delay
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1000);
            
        } catch (error) {
            console.error('Logout failed:', error);
            
            // Reset button state
            logoutBtn.textContent = originalText;
            logoutBtn.disabled = false;
            
            // Show error but still try to redirect (in case of network issues)
            this.showError('Logout failed, redirecting anyway...');
            
            setTimeout(() => {
                this.userAPI.removeAuthToken();
                window.location.href = 'login.php';
            }, 2000);
        }
    }

    handleOutsideClick(event) {
        // Close user dropdown if clicking outside
        if (this.isDropdownOpen && 
            !this.userProfileBtn.contains(event.target) && 
            !this.userDropdownMenu.contains(event.target)) {
            this.closeUserDropdown();
        }
        
        // Close mobile menu if clicking outside
        if (this.isMobileMenuOpen && 
            !this.mobileNavToggle.contains(event.target) && 
            !this.mobileNavMenu.contains(event.target)) {
            this.closeMobileMenu();
        }
    }

    handleEscapeKey(event) {
        if (event.key === 'Escape') {
            if (this.isDropdownOpen) {
                this.closeUserDropdown();
                this.userProfileBtn.focus();
            }
            
            if (this.isMobileMenuOpen) {
                this.closeMobileMenu();
                this.mobileNavToggle.focus();
            }
        }
    }

    showError(message) {
        if (typeof APIUtils !== 'undefined' && APIUtils.showError) {
            APIUtils.showError(message);
        } else {
            console.error(message);
            alert(message); // Fallback
        }
    }

    showSuccess(message) {
        if (typeof APIUtils !== 'undefined' && APIUtils.showSuccess) {
            APIUtils.showSuccess(message);
        } else {
            console.log(message);
        }
    }

    // Clean up when page is about to unload
    destroy() {
        this.stopNotificationPolling();
        
        // Remove event listeners
        if (this.mobileNavToggle) {
            this.mobileNavToggle.removeEventListener('click', this.handleMobileNavToggle);
        }
        
        if (this.userProfileBtn) {
            this.userProfileBtn.removeEventListener('click', this.handleUserProfileToggle);
        }
        
        this.logoutBtns.forEach(btn => {
            btn.removeEventListener('click', this.handleLogout);
        });
        
        if (this.notificationBtn) {
            this.notificationBtn.removeEventListener('click', this.handleNotificationClick);
        }
        
        document.removeEventListener('click', this.handleOutsideClick);
        document.removeEventListener('keydown', this.handleEscapeKey);
    }
}

// Clean up on page unload
window.addEventListener('beforeunload', () => {
    if (window.loggedInHeader) {
        window.loggedInHeader.destroy();
    }
});

// Export for global access if needed
if (typeof window !== 'undefined') {
    window.LoggedInHeader = LoggedInHeader;
}