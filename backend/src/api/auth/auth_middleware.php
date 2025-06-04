<?php
/**
 * Authentication Middleware
 * 
 * Verifies access token and authorizes API requests
 * Usage: require_once 'auth_middleware.php'; at the top of any protected API endpoint
 */

// Get database connection if not already available
if (!isset($Database) || !$Database) {
    $Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';
}

require_once dirname(__FILE__) . '/auth_utils.php';

/**
 * Authorize API request
 * 
 * @param bool $required Whether authentication is required
 * @return array|null User data if authenticated, null if authentication failed and not required
 */
function authorizeRequest($required = true) {
    global $Database;
    
    // Check database connection
    if (!$Database->isConnected()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection error']);
        exit();
    }    // Get authorization header
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    // Try alternative header formats if not found
    if (empty($authHeader) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    
    // Try the mod_rewrite environment variable (for some server configs)
    if (empty($authHeader) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
      // Try CGI/FastCGI environment variable
    if (empty($authHeader) && function_exists('apache_request_headers')) {
        $apacheHeaders = apache_request_headers();
        if (isset($apacheHeaders['Authorization'])) {
            $authHeader = $apacheHeaders['Authorization'];
        }
    }
    
    // Check if Authorization header exists
    if (empty($authHeader)) {
        if ($required) {
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit();
        }
        return null;
    }
    
    // Check if format is "Bearer [token]"
    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        if ($required) {
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'message' => 'Invalid authentication format']);
            exit();
        }
        return null;
    }
    
    $token = $matches[1];
    
    // Validate token
    $tokenData = AuthUtils::validateToken($token);
    if ($tokenData === false) {
        if ($required) {
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token', 'reset_token' => true]);
            exit();
        }
        return null;
    }
    
    // Check if token is blacklisted
    if (AuthUtils::isTokenBlacklisted($token, $Database)) {
        if ($required) {
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'message' => 'Token has been invalidated', 'reset_token' => true]);
            exit();
        }
        return null;
    }
    
    // Get user ID from token
    $userId = $tokenData['user_id'];
    
    // Get user from database
    $user = $Database->query(
        "SELECT user_id, username, email, account_status, role FROM users WHERE user_id = ?", 
        [$userId]
    );
    
    if ($user === false || empty($user)) {
        if ($required) {
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit();
        }
        return null;
    }
    
    // Get user data
    $user = $user[0];
    
    // Check account status
    if ($user['account_status'] !== 'active') {
        if ($required) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Account is not active']);
            exit();
        }
        return null;
    }
    
    // Return user data
    return $user;
}

// Add user data to global scope if requested
if (isset($requireAuth) && $requireAuth === true) {
    $authUser = authorizeRequest(true);
}
