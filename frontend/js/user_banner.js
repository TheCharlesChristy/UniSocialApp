function gotoProfile(username) {
    try {
        // Check if user is authenticated
        if (!userAPI.isAuthenticated()) {
            console.warn('User not authenticated, redirecting to login');
            window.location.href = '../pages/login.php';
            return;
        }

        console.log('Navigating to profile for user:', username);
        if (!username) {
            console.error('Username not found in the banner');
            return;
        }
        // window.location.href = '../pages/view-account.php?username=' + encodeURIComponent(username);
    } catch (error) {
        console.error('Error navigating to profile:', error);
        APIUtils.showError('Unable to access profile page');
    }
}