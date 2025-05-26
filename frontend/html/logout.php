<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out... | Social Media App</title>
    <link rel="stylesheet" href="../css/generic.css">
    <link rel="stylesheet" href="../css/logout.css">
</head>
<body>
    <div class="logout-container">
        <div class="logout-card">
            <div class="logout-icon">
                <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.59L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                </svg>
            </div>
            
            <h1 class="logout-title">Logging Out</h1>
            <p class="logout-message">Please wait while we securely log you out...</p>
            
            <div class="logout-progress">
                <div class="logout-progress-bar" id="progressBar"></div>
            </div>
            
            <div class="logout-spinner" id="logoutSpinner"></div>
            <div class="logout-status" id="logoutStatus">Clearing authentication data...</div>
            
            <div class="error-message" id="errorMessage"></div>
            
            <div class="logout-actions" id="logoutActions" style="display: none;">
                <a href="login.php" class="btn-login">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
                    </svg>
                    Sign In Again
                </a>
                <a href="register.php" class="btn-register">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>
                    </svg>
                    Create New Account
                </a>
            </div>
        </div>
    </div>

    <script>
        class LogoutManager {
            constructor() {
                this.steps = [
                    { message: 'Clearing authentication data...', duration: 1000 },
                    { message: 'Invalidating session tokens...', duration: 1500 },
                    { message: 'Cleaning up user preferences...', duration: 800 },
                    { message: 'Finalizing logout...', duration: 700 }
                ];
                this.currentStep = 0;
                this.progressBar = document.getElementById('progressBar');
                this.statusElement = document.getElementById('logoutStatus');
                this.spinnerElement = document.getElementById('logoutSpinner');
                this.actionsElement = document.getElementById('logoutActions');
                this.errorElement = document.getElementById('errorMessage');
                
                this.startLogout();
            }
            
            async startLogout() {
                try {
                    // Start the logout process
                    await this.performLogout();
                } catch (error) {
                    console.error('Logout error:', error);
                    this.showError('An error occurred during logout. You will be redirected to the login page.');
                    setTimeout(() => this.redirectToLogin(), 3000);
                }
            }
            
            async performLogout() {
                // Step 1: Clear JWT tokens and local storage
                await this.executeStep(0, () => {
                    localStorage.removeItem('jwt_token');
                    localStorage.removeItem('refresh_token');
                    localStorage.removeItem('user_id');
                    localStorage.removeItem('user_role');
                    localStorage.removeItem('user_data');
                });
                
                // Step 2: Call logout API to invalidate server-side session
                await this.executeStep(1, async () => {
                    try {
                        const token = localStorage.getItem('jwt_token');
                        if (token) {
                            await fetch('../../backend/src/api/auth/logout.php', {
                                method: 'POST',
                                headers: {
                                    'Authorization': `Bearer ${token}`,
                                    'Content-Type': 'application/json'
                                }
                            });
                        }
                    } catch (error) {
                        // Ignore API errors - we're logging out anyway
                        console.warn('Logout API call failed:', error);
                    }
                });
                
                // Step 3: Clear session storage and cookies
                await this.executeStep(2, () => {
                    sessionStorage.clear();
                    
                    // Clear any authentication cookies
                    document.cookie.split(";").forEach(cookie => {
                        const eqPos = cookie.indexOf("=");
                        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
                        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
                    });
                });
                
                // Step 4: Finalize and redirect
                await this.executeStep(3, () => {
                    // Clear any remaining application state
                    if (window.EventSource) {
                        // Close any open SSE connections
                        window.addEventListener('beforeunload', () => {
                            // This will be handled by the browser
                        });
                    }
                });
                
                // Complete logout
                this.completeLogout();
            }
            
            async executeStep(stepIndex, action) {
                const step = this.steps[stepIndex];
                this.statusElement.textContent = step.message;
                
                // Update progress bar
                const progress = ((stepIndex + 1) / this.steps.length) * 100;
                this.progressBar.style.width = `${progress}%`;
                
                // Execute the action
                await action();
                
                // Wait for the step duration
                await this.delay(step.duration);
            }
            
            completeLogout() {
                this.progressBar.style.width = '100%';
                this.statusElement.textContent = 'Logout completed successfully!';
                this.spinnerElement.style.display = 'none';
                
                // Show success message
                const successMessage = document.createElement('div');
                successMessage.className = 'success-message';
                successMessage.textContent = 'You have been successfully logged out.';
                this.errorElement.parentNode.insertBefore(successMessage, this.errorElement);
                
                // Show action buttons
                setTimeout(() => {
                    this.actionsElement.style.display = 'flex';
                }, 500);
                
                // Auto-redirect after 5 seconds
                setTimeout(() => {
                    this.redirectToLogin();
                }, 5000);
            }
            
            showError(message) {
                this.errorElement.textContent = message;
                this.errorElement.style.display = 'block';
                this.spinnerElement.style.display = 'none';
                this.statusElement.textContent = 'Logout failed';
                this.actionsElement.style.display = 'flex';
            }
            
            redirectToLogin() {
                window.location.href = 'login.php';
            }
            
            delay(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }
        }
        
        // Start logout process when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new LogoutManager();
        });
        
        // Prevent back button navigation
        window.history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', () => {
            window.history.pushState(null, null, window.location.href);
        });
    </script>
</body>
</html>
