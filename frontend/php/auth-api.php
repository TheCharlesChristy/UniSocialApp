<?php

require_once 'api-handler.php';

class AuthAPI extends APIHandler {
    
    public function __construct() {
        // Initialize with auth-specific base URL
        parent::__construct('/webdev/backend/src/api/auth');
    }

    /**
     * Register a new user account
     * 
     * @param string $username Unique username (alphanumeric)
     * @param string $email Valid email address
     * @param string $password Password (min 8 chars, letters + numbers)
     * @param string $firstName User's first name
     * @param string $lastName User's last name
     * @param string $dateOfBirth Date of birth (YYYY-MM-DD format)
     * @return array Response data
     * @throws Exception If registration fails
     */
    public function register($username, $email, $password, $firstName, $lastName, $dateOfBirth) {
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'date_of_birth' => $dateOfBirth
        ];

        return $this->post('/register', $data);
    }

    /**
     * Login user with email/username and password
     * 
     * @param string $emailOrUsername User's email or username
     * @param string $password User's password
     * @param bool $isEmail Whether the first parameter is email (true) or username (false)
     * @return array Response data including token
     * @throws Exception If login fails
     */
    public function login($emailOrUsername, $password, $isEmail = true) {
        $data = [
            'password' => $password
        ];

        if ($isEmail) {
            $data['email'] = $emailOrUsername;
        } else {
            $data['username'] = $emailOrUsername;
        }

        $response = $this->post('/login', $data);
        
        // Automatically store the token if login is successful
        if (isset($response['token'])) {
            $this->setAuthToken($response['token']);
        }

        return $response;
    }

    /**
     * Login user with email and password
     * 
     * @param string $email User's email
     * @param string $password User's password
     * @return array Response data including token
     */
    public function loginWithEmail($email, $password) {
        return $this->login($email, $password, true);
    }

    /**
     * Login user with username and password
     * 
     * @param string $username User's username
     * @param string $password User's password
     * @return array Response data including token
     */
    public function loginWithUsername($username, $password) {
        return $this->login($username, $password, false);
    }

    /**
     * Logout current user and invalidate token
     * 
     * @return array Response data
     * @throws Exception If logout fails
     */
    public function logout() {
        $token = $this->getAuthToken();
        if (!$token) {
            throw new Exception('No authentication token found');
        }

        $data = [
            'token' => $token
        ];

        $response = $this->post('/logout', $data);
        
        // Remove token after successful logout
        $this->removeAuthToken();
        
        return $response;
    }

    /**
     * Request password reset email
     * 
     * @param string $email User's email address
     * @return array Response data
     * @throws Exception If request fails
     */
    public function forgotPassword($email) {
        $data = [
            'email' => $email
        ];

        return $this->post('/forgot-password', $data);
    }

    /**
     * Reset password using reset token
     * 
     * @param string $token Password reset token from email
     * @param string $newPassword New password (min 8 chars, letters + numbers)
     * @return array Response data
     * @throws Exception If reset fails
     */
    public function resetPassword($token, $newPassword) {
        $data = [
            'token' => $token,
            'new_password' => $newPassword
        ];

        return $this->post('/reset-password', $data);
    }

    /**
     * Verify email using verification token
     * 
     * @param string $token Email verification token
     * @return array Response data
     * @throws Exception If verification fails
     */
    public function verifyEmail($token) {
        return $this->get("/verify-email/$token");
    }

    /**
     * Validate current authentication token
     * 
     * @return array Response data
     * @throws Exception If validation fails
     */
    public function validateToken() {
        return $this->authenticatedRequest('/validate', [
            'method' => 'GET'
        ]);
    }

    /**
     * Test access to protected endpoint (for testing authentication)
     * 
     * @return array Response data
     * @throws Exception If access denied
     */
    public function testAccess() {
        return $this->authenticatedRequest('/test_access', [
            'method' => 'GET'
        ]);
    }

    /**
     * Get current user information (if authenticated)
     * 
     * @return array|null User data or null if not authenticated
     */
    public function getCurrentUser() {
        try {
            if (!$this->isAuthenticated()) {
                return null;
            }
            
            $response = $this->validateToken();
            return $response['user'] ?? null;
        } catch (Exception $e) {
            // Token might be expired or invalid
            $this->removeAuthToken();
            return null;
        }
    }

    /**
     * Check if user has a valid session
     * 
     * @return bool True if user is authenticated with valid token
     */
    public function hasValidSession() {
        try {
            if (!$this->isAuthenticated()) {
                return false;
            }
            
            $this->validateToken();
            return true;
        } catch (Exception $e) {
            // Token is invalid or expired
            $this->removeAuthToken();
            return false;
        }
    }

    /**
     * Refresh authentication by validating current token
     * 
     * @return bool True if token is still valid
     */
    public function refreshAuth() {
        return $this->hasValidSession();
    }

    /**
     * Get user ID from current session
     * 
     * @return int|null User ID or null if not authenticated
     */
    public function getCurrentUserId() {
        $user = $this->getCurrentUser();
        return $user['user_id'] ?? null;
    }

    /**
     * Login and remember user (sets persistent cookie)
     * 
     * @param string $emailOrUsername User's email or username
     * @param string $password User's password
     * @param bool $isEmail Whether the first parameter is email (true) or username (false)
     * @return array Response data including token
     */
    public function loginAndRemember($emailOrUsername, $password, $isEmail = true) {
        $response = $this->login($emailOrUsername, $password, $isEmail);
        
        // Set persistent token if login successful
        if (isset($response['token'])) {
            $this->setAuthToken($response['token'], true);
        }

        return $response;
    }

    /**
     * Auto-login from persistent session (check cookie)
     * 
     * @return bool True if successfully logged in from persistent session
     */
    public function autoLogin() {
        // Check if we have a cookie token but no session token
        if (!$this->isAuthenticated() && isset($_COOKIE['auth_token'])) {
            $this->setAuthToken($_COOKIE['auth_token']);
            return $this->hasValidSession();
        }
        
        return $this->hasValidSession();
    }

    /**
     * Complete logout (removes both session and persistent cookie)
     * 
     * @return array Response data
     */
    public function completeLogout() {
        try {
            $response = $this->logout();
            return $response;
        } catch (Exception $e) {
            // Even if logout fails, remove local tokens
            $this->removeAuthToken();
            throw $e;
        }
    }
}