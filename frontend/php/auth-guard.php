<?php
/**
 * Authentication Guard
 * Include this file in any page that requires user authentication.
 * It will handle JWT token syncing from localStorage to cookies and redirect unauthenticated users.
 */

// Include API handler if not already included
if (!class_exists('APIHandler')) {
    require_once __DIR__ . '/api-handler.php';
}

// Initialize API handler
$apiHandler = new APIHandler();

// Check if we already have a token in cookies (from previous visits)
if (!isset($_COOKIE['auth_token']) || empty($_COOKIE['auth_token'])) {
    // No cookie token - need to check localStorage via JavaScript first
    ?>
    <div style="text-align: center; margin-top: 50px;">Loading...</div>
    
    <script>
    (function() {
        // Check for token in localStorage/sessionStorage
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
        
        if (!token) {
            // No token found anywhere, redirect to login
            window.location.href = 'login.php';
            return;
        }
        
        // Token found - sync it to cookie and reload page
        const secure = location.protocol === 'https:';
        document.cookie = `auth_token=${token}; path=/; max-age=${30 * 24 * 60 * 60}; ${secure ? 'secure;' : ''} samesite=lax`;
        
        // Reload the page so PHP can read the cookie
        window.location.reload();
    })();
    </script>
    <?php
    exit(); // Stop PHP execution until token is synced
}

// Check if user is authenticated
if (!$apiHandler->isAuthenticated()) {
    // Clear invalid token and redirect
    $apiHandler->removeAuthToken();
    ?>
    <script>
    localStorage.removeItem('auth_token');
    sessionStorage.removeItem('auth_token');
    window.location.href = 'login.php';
    </script>
    <?php
    exit();
}

// User is authenticated - $apiHandler is available for use in the including page
?>
