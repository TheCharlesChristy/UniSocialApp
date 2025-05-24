<?php
/**
 * User API Endpoints Test Page
 * 
 * This page provides a simple interface to test all user endpoints
 * and displays the results in a formatted way.
 */

// Start or resume the session for token persistence
session_start();

// Handle AJAX token saving
if (isset($_POST['save_token']) && isset($_POST['token'])) {
    $_SESSION['global_token'] = $_POST['token'];
    exit; // No need to process the rest of the page for AJAX requests
}

// Define constants
// Apache isn't accessing the full path correctly, adjusting URL
define('BASE_API_URL', 'http://localhost/webdev/backend/src/api/users/');
define('ADJUSTED_API_URL', true); // Set to false if original URL should be used

// Function to get the proper API URL
function getApiUrl($endpoint, $pathParams = []) {
    $url = BASE_API_URL;
    
    if (ADJUSTED_API_URL) {
        // Handle path parameters for endpoints
        switch ($endpoint) {            case 'get_user':
                $userId = !empty($pathParams['userId']) ? $pathParams['userId'] : '';
                if (!empty($userId)) {
                    // For URL format /api/users/:userId
                    // The correct approach is to simply append the parameter to the request
                    return BASE_API_URL . 'get_user.php?userId=' . $userId;
                }
                return BASE_API_URL . 'get_user.php';                  case 'get_user_posts':
                $userId = !empty($pathParams['userId']) ? $pathParams['userId'] : '';
                if (!empty($userId)) {
                    // For URL format /api/users/:userId/posts
                    // The correct approach is to simply append the parameter to the request
                    return BASE_API_URL . 'get_user_posts.php?userId=' . $userId;
                }                
                return BASE_API_URL . 'get_user_posts.php';
                
            case 'update_profile':
                return BASE_API_URL . 'me.php';
                
            case 'update_password':
                return BASE_API_URL . 'update_password.php';
                
            case 'search_users':
                return BASE_API_URL . 'search_users.php';
                
            case 'get_suggestions':
                return BASE_API_URL . 'get_suggestions.php';
                  case 'me':
                return BASE_API_URL . 'me.php';
                
            case 'auth_test':
                return BASE_API_URL . 'me.php';
                
            default:
                // Regular endpoints with .php extension
                return BASE_API_URL . $endpoint . '.php';
        }
    } else {
        // RESTful URLs without .php extension
        switch ($endpoint) {            case 'get_user':
                $userId = !empty($pathParams['userId']) ? $pathParams['userId'] : '';
                if (!empty($userId)) {
                    return BASE_API_URL . '?userId=' . $userId;
                }
                return BASE_API_URL;
                
            case 'get_user_posts':
                $userId = !empty($pathParams['userId']) ? $pathParams['userId'] : '';
                if (!empty($userId)) {
                    return BASE_API_URL . 'posts?userId=' . $userId;
                }
                return BASE_API_URL . 'posts';
                
            case 'update_profile':
                return BASE_API_URL . 'me';
                
            case 'update_password':
                return BASE_API_URL . 'me/password';
                
            case 'search_users':
                return BASE_API_URL . 'search';
                  case 'get_suggestions':
                return BASE_API_URL . 'suggestions';
                
            case 'me':
                return BASE_API_URL . 'me';
                
            case 'auth_test':
                return BASE_API_URL . 'me';
                
            default:
                return BASE_API_URL . $endpoint;
        }
    }
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$activeTab = $_GET['tab'] ?? 'get_user';
$response = null;
$responseCode = null;
$responseTime = null;
$requestBody = null;
$requestUrl = null;
$rawResponse = null;
$headers = [];
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the endpoint and data
    $endpoint = $_POST['endpoint'] ?? '';
    $data = json_decode($_POST['data'] ?? '{}', true);
    $token = $_POST['token'] ?? '';
    $requestBody = $_POST['data'];
    $method = $_POST['method'] ?? 'GET';
    $pathParams = [];
    
    // Save the token to session for persistence between requests
    if (!empty($token)) {
        $_SESSION['global_token'] = $token;
    }
    
    // Extract path parameters based on the endpoint
    if ($endpoint === 'get_user' || $endpoint === 'get_user_posts') {
        $pathParams['userId'] = isset($data['userId']) ? $data['userId'] : '';
        unset($data['userId']);  // Remove from body data if it exists
    }
    
    // Add Authorization header
    if (!empty($token)) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    // Set the active tab based on the endpoint
    $activeTab = $endpoint;
    
    // Make the API request
    $startTime = microtime(true);
    $responseData = makeRequest($endpoint, $data, $headers, $method, $pathParams);
    $responseTime = round((microtime(true) - $startTime) * 1000); // in milliseconds
    $responseCode = $responseData['code'];
    $response = $responseData['body'];
    $requestUrl = $responseData['url'] ?? '';
    $rawResponse = $responseData['raw_response'] ?? '';
    
    // Set message based on response
    if (isset($response['success']) && $response['success']) {
        $message = '<div class="alert alert-success">Request successful!</div>';
    } else {
        $message = '<div class="alert alert-danger">Request failed: ' . 
            (isset($response['message']) ? htmlspecialchars($response['message']) : 'Unknown error') . '</div>';
    }
}

/**
 * Make an API request
 * 
 * @param string $endpoint API endpoint
 * @param array $data Request data
 * @param array $headers Request headers
 * @param string $method HTTP method
 * @param array $pathParams Path parameters for URL construction
 * @return array Response data
 */
function makeRequest($endpoint, $data = [], $headers = [], $method = 'GET', $pathParams = []) {
    // Use the URL formatting function
    $url = getApiUrl($endpoint, $pathParams);
    
    // For GET requests with query parameters
    if ($method === 'GET' && !empty($data)) {
        // Check if the URL already has a query parameter (contains ?)
        $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
    }
    
    // Set default headers
    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    $headers = array_merge($defaultHeaders, $headers);
    
    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if any
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set a timeout
    
    // Add data for POST/PUT requests
    if (($method === 'POST' || $method === 'PUT') && !empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    // Execute the request
    $response = curl_exec($ch);
      // Check for cURL errors
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'code' => 0,
            'body' => ['success' => false, 'message' => "cURL Error: $error", 'url' => $url]
        ];
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $headerSize);
    
    // Close cURL
    curl_close($ch);
    
    // Process the response
    $responseBody = json_decode($body, true);
      // Check if response is valid JSON
    if ($responseBody === null && json_last_error() !== JSON_ERROR_NONE) {
        // Not JSON or invalid JSON, return the raw response with error info
        return [
            'code' => $httpCode,
            'body' => [
                'success' => false, 
                'message' => 'Invalid JSON response: ' . json_last_error_msg(),
                'raw_response' => $body,
                'url' => $url,
                'method' => $method
            ]
        ];
    }
    
    return [
        'code' => $httpCode,
        'body' => $responseBody ?: ['success' => false, 'message' => 'Empty response'],
        'url' => $url,
        'raw_response' => $body
    ];
}

/**
 * Format JSON for display
 * 
 * @param string|array $json JSON data
 * @return string Formatted JSON
 */
function formatJson($json) {
    if (is_array($json)) {
        $json = json_encode($json, JSON_PRETTY_PRINT);
    }
    
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen($json);

    for ($i = 0; $i < $json_length; $i++) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        
        if ($ends_line_level !== NULL) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        
        if ($in_escape) {
            $in_escape = false;
        } else if ($char === '"') {
            $in_quotes = !$in_quotes;
        } else if (!$in_quotes) {
            switch ($char) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;
                
                case '{': case '[':
                    $level++;
                    // no break
                case ',':
                    $ends_line_level = $level;
                    break;
                    
                case ':':
                    $post = " ";
                    break;
                    
                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ($char === '\\') {
            $in_escape = true;
        }
        
        if ($new_line_level !== NULL) {
            $result .= "\n" . str_repeat("    ", $new_line_level);
        }
        
        $result .= $char . $post;
    }

    return $result;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User API Test Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 40px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        pre {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            max-height: 400px;
            overflow: auto;
        }
        .pre-scrollable {
            max-height: 340px;
            overflow-y: scroll;
        }
        .response-container {
            margin-top: 20px;
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .endpoint-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .response-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .badge {
            font-size: 14px;
        }
        .back-to-tests-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: #007bff;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            z-index: 1000;
            transition: background-color 0.3s;
        }
        .back-to-tests-btn:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <a href="http://localhost/webdev/tests" class="back-to-tests-btn">← Back to Tests</a>
    <div class="container">
        <h1 class="text-center mb-4">User API Test Page</h1>
        
        <?php echo $message; ?>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">                        <h5 class="card-title">Authentication Token</h5>
                        <p class="card-text text-muted">This token will be used for all requests. You can get a token by logging in through the Auth API Test Page.</p>
                        <form id="tokenForm">
                            <div class="form-group">
                                <textarea class="form-control" id="global-token" rows="3" placeholder="Paste your authentication token here"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
          <ul class="nav nav-tabs" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'get_user' ? 'active' : '' ?>" id="get-user-tab" data-bs-toggle="tab" data-bs-target="#get-user" type="button" role="tab">Get User Profile</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'update_profile' ? 'active' : '' ?>" id="update-profile-tab" data-bs-toggle="tab" data-bs-target="#update-profile" type="button" role="tab">Update Profile</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'update_password' ? 'active' : '' ?>" id="update-password-tab" data-bs-toggle="tab" data-bs-target="#update-password" type="button" role="tab">Update Password</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'get_user_posts' ? 'active' : '' ?>" id="get-user-posts-tab" data-bs-toggle="tab" data-bs-target="#get-user-posts" type="button" role="tab">Get User Posts</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'search_users' ? 'active' : '' ?>" id="search-users-tab" data-bs-toggle="tab" data-bs-target="#search-users" type="button" role="tab">Search Users</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'get_suggestions' ? 'active' : '' ?>" id="get-suggestions-tab" data-bs-toggle="tab" data-bs-target="#get-suggestions" type="button" role="tab">User Suggestions</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'me' ? 'active' : '' ?>" id="me-tab" data-bs-toggle="tab" data-bs-target="#me" type="button" role="tab">Current User</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'auth_test' ? 'active' : '' ?>" id="auth-test-tab" data-bs-toggle="tab" data-bs-target="#auth-test" type="button" role="tab">Auth Test</button>
            </li>
        </ul>
        
        <div class="tab-content" id="userTabsContent">
            <!-- Get User Profile Tab -->
            <div class="tab-pane fade <?= $activeTab === 'get_user' ? 'show active' : '' ?>" id="get-user" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Get User Profile</h3>
                    <p class="text-muted">Endpoint: GET /api/users/:userId</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="get_user">
                    <input type="hidden" name="method" value="GET">
                      <div class="form-group">
                        <label for="get-user-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="get-user-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="get-user-data">Request Parameters:</label>
                        <textarea class="form-control" id="get-user-data" name="data" rows="4">{
    "userId": "1"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Update Profile Tab -->
            <div class="tab-pane fade <?= $activeTab === 'update_profile' ? 'show active' : '' ?>" id="update-profile" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Update User Profile</h3>
                    <p class="text-muted">Endpoint: PUT /api/users/me</p>
                    <p class="text-info">Note: For profile picture uploads, use a proper form with file input instead of this JSON-based testing tool.</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="update_profile">
                    <input type="hidden" name="method" value="PUT">
                      <div class="form-group">
                        <label for="update-profile-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="update-profile-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="update-profile-data">Request Body:</label>
                        <textarea class="form-control" id="update-profile-data" name="data" rows="8">{
    "first_name": "John",
    "last_name": "Doe",
    "bio": "This is my updated bio"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Update Password Tab -->
            <div class="tab-pane fade <?= $activeTab === 'update_password' ? 'show active' : '' ?>" id="update-password" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Update User Password</h3>
                    <p class="text-muted">Endpoint: PUT /api/users/me/password</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="update_password">
                    <input type="hidden" name="method" value="PUT">
                      <div class="form-group">
                        <label for="update-password-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="update-password-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="update-password-data">Request Body:</label>
                        <textarea class="form-control" id="update-password-data" name="data" rows="6">{
    "current_password": "Password123",
    "new_password": "NewPassword123"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Get User Posts Tab -->
            <div class="tab-pane fade <?= $activeTab === 'get_user_posts' ? 'show active' : '' ?>" id="get-user-posts" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Get User Posts</h3>
                    <p class="text-muted">Endpoint: GET /api/users/:userId/posts</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="get_user_posts">
                    <input type="hidden" name="method" value="GET">
                      <div class="form-group">
                        <label for="get-user-posts-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="get-user-posts-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="get-user-posts-data">Request Parameters:</label>
                        <textarea class="form-control" id="get-user-posts-data" name="data" rows="6">{
    "userId": "1",
    "page": "1",
    "limit": "10"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Search Users Tab -->
            <div class="tab-pane fade <?= $activeTab === 'search_users' ? 'show active' : '' ?>" id="search-users" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Search Users</h3>
                    <p class="text-muted">Endpoint: GET /api/users/search</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="search_users">
                    <input type="hidden" name="method" value="GET">
                      <div class="form-group">
                        <label for="search-users-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="search-users-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="search-users-data">Request Parameters:</label>
                        <textarea class="form-control" id="search-users-data" name="data" rows="6">{
    "query": "john",
    "page": "1",
    "limit": "10"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- User Suggestions Tab -->
            <div class="tab-pane fade <?= $activeTab === 'get_suggestions' ? 'show active' : '' ?>" id="get-suggestions" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Get User Suggestions</h3>
                    <p class="text-muted">Endpoint: GET /api/users/suggestions</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="get_suggestions">
                    <input type="hidden" name="method" value="GET">
                      <div class="form-group">
                        <label for="get-suggestions-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="get-suggestions-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="get-suggestions-data">Request Parameters:</label>
                        <textarea class="form-control" id="get-suggestions-data" name="data" rows="4">{
    "limit": "10"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
              <!-- Current User Tab -->
            <div class="tab-pane fade <?= $activeTab === 'me' ? 'show active' : '' ?>" id="me" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Get Current User</h3>
                    <p class="text-muted">Endpoint: GET /api/users/me</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="me">
                    <input type="hidden" name="method" value="GET">
                      <div class="form-group">
                        <label for="me-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="me-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="me-data">Request Parameters:</label>
                        <textarea class="form-control" id="me-data" name="data" rows="3">{
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Auth Test Tab -->
            <div class="tab-pane fade <?= $activeTab === 'auth_test' ? 'show active' : '' ?>" id="auth-test" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Authentication Test</h3>
                    <p class="text-muted">Endpoint: GET /api/users/me</p>
                    <p class="text-info">This tab is specifically for validating authentication. Provide a token and test if it's valid.</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="auth_test">
                    <input type="hidden" name="method" value="GET">
                      <div class="form-group">
                        <label for="auth-test-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="auth-test-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="auth-test-data">Request Parameters (None Required):</label>
                        <textarea class="form-control" id="auth-test-data" name="data" rows="3">{
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Test Authentication</button>
                </form>
            </div>
        </div>
        
        <?php if ($response !== null): ?>
        <div class="response-container mt-4">
            <h4>Response</h4>
            <div class="response-info">
                <div>
                    <span class="badge bg-<?= ($responseCode >= 200 && $responseCode < 300) ? 'success' : 'danger' ?>">
                        Status: <?= $responseCode ?>
                    </span>
                </div>
                <div>
                    <span class="badge bg-secondary">Time: <?= $responseTime ?> ms</span>
                </div>
            </div>
            
            <?php if ($requestUrl): ?>
            <h5 class="mt-3">Request URL</h5>
            <pre class="pre-scrollable"><code><?= htmlspecialchars($requestUrl) ?></code></pre>
            <?php endif; ?>
            
            <?php if ($requestBody): ?>
            <h5 class="mt-3">Request Body</h5>
            <pre class="pre-scrollable"><code><?= htmlspecialchars(formatJson($requestBody)) ?></code></pre>
            <?php endif; ?>
            
            <h5 class="mt-3">Response Body</h5>
            <pre class="pre-scrollable"><code><?= htmlspecialchars(formatJson($response)) ?></code></pre>
        </div>
        <?php endif; ?>
        
        <div class="mt-5">
            <h4>How to use this test page</h4>
            <ol>
                <li>First, get an authentication token by using the Auth API Test Page and logging in.</li>
                <li>Paste your token in the Authentication Token field at the top of the page.</li>
                <li>Select the endpoint you want to test from the tabs above.</li>
                <li>Fill in the required parameters in the JSON field.</li>
                <li>Click "Send Request" to execute the API call.</li>
                <li>The response will be displayed at the bottom of the page.</li>
            </ol>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>    <script>
        // Copy the global token to individual token fields
        document.addEventListener('DOMContentLoaded', function() {
            const globalToken = document.getElementById('global-token');
            const tokenFields = document.querySelectorAll('.token-field');
            
            // Initialize token fields with the global token value
            const initialToken = globalToken.value.trim();
            if (initialToken) {
                tokenFields.forEach(field => {
                    field.value = initialToken;
                });
            }
            
            globalToken.addEventListener('input', function() {
                const token = globalToken.value.trim();
                tokenFields.forEach(field => {
                    field.value = token;
                });
                
                // Save token to session via AJAX to ensure it persists even without form submission
                if (token) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', window.location.href, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send('save_token=1&token=' + encodeURIComponent(token));
                }
            });
            
            // Check for token in URL hash (e.g., from auth redirect)
            if(window.location.hash) {
                const hash = window.location.hash.substring(1);
                if(hash.startsWith('token=')) {
                    const token = decodeURIComponent(hash.substring(6));
                    globalToken.value = token;
                    tokenFields.forEach(field => {
                        field.value = token;
                    });
                }
            }
        });
    </script>
</body>
</html>
