<?php
/**
 * Authentication Endpoints Test Page
 * 
 * This page provides a simple interface to test all authentication endpoints
 * and displays the results in a formatted way.
 */

// Define constants
// Apache isn't accessing the full path correctly, adjusting URL
define('BASE_API_URL', 'http://localhost/webdev/backend/src/api/auth/');
define('ADJUSTED_API_URL', true); // Set to false if original URL should be used

// Function to get the proper API URL
function getApiUrl($endpoint) {
    if (ADJUSTED_API_URL) {
        // Add .php extension to endpoints
        return BASE_API_URL . $endpoint . '.php';
    }
    return BASE_API_URL . $endpoint;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$activeTab = $_GET['tab'] ?? 'register';
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

    // Add Authorization header if token provided
    if (!empty($token) && $endpoint !== 'logout') {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    // Set the active tab based on the endpoint
    switch ($endpoint) {
        case 'register':
            $activeTab = 'register';
            break;
        case 'login':
        case 'logout':
        case 'forgot-password':
        case 'reset-password':
            $activeTab = $endpoint;
            break;
        case 'verify-email':
            $activeTab = 'verify';
            break;
        default:
            $activeTab = 'register';
    }
    
    // Make the API request
    $startTime = microtime(true);
    $responseData = makeRequest($endpoint, $data, $headers);
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
 * @return array Response data
 */
function makeRequest($endpoint, $data = [], $headers = []) {
    // Use the URL formatting function
    $url = getApiUrl($endpoint);
    
    // Special case for verify-email which uses GET method
    if ($endpoint === 'verify-email') {
        $token = isset($data['token']) ? $data['token'] : '';
        if (!empty($token)) {
            // Handle verify-email endpoint specially
            if (ADJUSTED_API_URL) {
                $url = BASE_API_URL . 'verify-email.php/' . urlencode($token);
            } else {
                $url = BASE_API_URL . 'verify-email/' . urlencode($token);
            }
        }
        $method = 'GET';
    } else {
        $method = 'POST';
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
    
    // Add POST data if method is POST
    if ($method === 'POST' && !empty($data)) {
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
            'body' => ['success' => false, 'message' => "cURL Error: $error"]
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
                'raw_response' => $body
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
    <title>Auth API Test Page</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Authentication API Test Page</h1>
        
        <?php echo $message; ?>
        
        <ul class="nav nav-tabs" id="authTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'register' ? 'active' : '' ?>" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">Register</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'login' ? 'active' : '' ?>" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">Login</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'logout' ? 'active' : '' ?>" id="logout-tab" data-bs-toggle="tab" data-bs-target="#logout" type="button" role="tab">Logout</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'forgot-password' ? 'active' : '' ?>" id="forgot-password-tab" data-bs-toggle="tab" data-bs-target="#forgot-password" type="button" role="tab">Forgot Password</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'reset-password' ? 'active' : '' ?>" id="reset-password-tab" data-bs-toggle="tab" data-bs-target="#reset-password" type="button" role="tab">Reset Password</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'verify' ? 'active' : '' ?>" id="verify-tab" data-bs-toggle="tab" data-bs-target="#verify" type="button" role="tab">Verify Email</button>
            </li>
        </ul>
        
        <div class="tab-content" id="authTabsContent">
            <!-- Register Tab -->            <div class="tab-pane fade <?= $activeTab === 'register' ? 'show active' : '' ?>" id="register" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Register User</h3>
                    <p class="text-muted">Endpoint: POST /api/auth/register</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="register">                    <div class="form-group">
                        <label for="register-data">Request Body:</label>
                        <textarea class="form-control" id="register-data" name="data" rows="10">{
    "username": "testuser",
    "email": "test@example.com",
    "password": "Password123",
    "first_name": "Test",
    "last_name": "User",
    "date_of_birth": "1990-01-01"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Login Tab -->
            <div class="tab-pane fade <?= $activeTab === 'login' ? 'show active' : '' ?>" id="login" role="tabpanel">
                <div class="endpoint-header">
                    <h3>User Login</h3>
                    <p class="text-muted">Endpoint: POST /api/auth/login</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="login">
                    <div class="form-group">
                        <label for="login-data">Request Body:</label>
                        <textarea class="form-control" id="login-data" name="data" rows="6">{
    "email": "test@example.com",
    "password": "Password123"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Logout Tab -->
            <div class="tab-pane fade <?= $activeTab === 'logout' ? 'show active' : '' ?>" id="logout" role="tabpanel">
                <div class="endpoint-header">
                    <h3>User Logout</h3>
                    <p class="text-muted">Endpoint: POST /api/auth/logout</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="logout">
                    <div class="form-group">
                        <label for="logout-token">Authentication Token:</label>
                        <textarea class="form-control" id="logout-token" name="token" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="logout-data">Request Body:</label>
                        <textarea class="form-control" id="logout-data" name="data" rows="4">{
    "token": "[PASTE_TOKEN_HERE]"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Forgot Password Tab -->
            <div class="tab-pane fade <?= $activeTab === 'forgot-password' ? 'show active' : '' ?>" id="forgot-password" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Forgot Password</h3>
                    <p class="text-muted">Endpoint: POST /api/auth/forgot-password</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="forgot-password">
                    <div class="form-group">
                        <label for="forgot-password-data">Request Body:</label>
                        <textarea class="form-control" id="forgot-password-data" name="data" rows="4">{
    "email": "test@example.com"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Reset Password Tab -->
            <div class="tab-pane fade <?= $activeTab === 'reset-password' ? 'show active' : '' ?>" id="reset-password" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Reset Password</h3>
                    <p class="text-muted">Endpoint: POST /api/auth/reset-password</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="reset-password">
                    <div class="form-group">
                        <label for="reset-password-data">Request Body:</label>
                        <textarea class="form-control" id="reset-password-data" name="data" rows="5">{
    "token": "[PASTE_RESET_TOKEN_HERE]",
    "new_password": "NewPassword123"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Verify Email Tab -->
            <div class="tab-pane fade <?= $activeTab === 'verify' ? 'show active' : '' ?>" id="verify" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Verify Email</h3>
                    <p class="text-muted">Endpoint: GET /api/auth/verify-email/:token</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="verify-email">
                    <div class="form-group">
                        <label for="verify-token">Verification Token:</label>
                        <textarea class="form-control" id="verify-token" name="data" rows="4">{
                            "token": "[PASTE_VERIFICATION_TOKEN_HERE]"
                        }</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
        </div>
        
        <?php if ($response !== null): ?>        <div class="response-container mt-4">
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
            
            <h5 class="mt-3">Response Body (Parsed)</h5>
            <pre class="pre-scrollable"><code><?= htmlspecialchars(formatJson($response)) ?></code></pre>
            
            <?php if ($rawResponse): ?>
            <h5 class="mt-3">Raw Response</h5>
            <pre class="pre-scrollable"><code><?= htmlspecialchars($rawResponse) ?></code></pre>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="mt-5">
            <h4>How to use this test page</h4>
            <ol>
                <li>Use the "Register" tab to create a new user account.</li>
                <li>Log in using the credentials you registered with.</li>
                <li>Copy the token from the login response to use in other authenticated requests.</li>
                <li>Test the "Me" endpoint to verify your authentication token works.</li>
                <li>For password reset, first request a reset token with "Forgot Password", then check the email_logs.txt file in the auth folder.</li>
                <li>For email verification, check the email_logs.txt file in the auth folder for the verification token.</li>
            </ol>
            <p class="text-muted">Note: In a development environment, emails are not actually sent. Instead, tokens are logged to the email_logs.txt file in the auth directory.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-update the token field in the logout form when a token is received
        document.addEventListener('DOMContentLoaded', function() {
            const response = document.querySelector('.response-container code');
            if (response) {
                try {
                    const responseData = JSON.parse(response.textContent);
                    if (responseData.token) {
                        // Update all token fields
                        document.querySelectorAll('#logout-token, #me-token').forEach(function(el) {
                            el.value = responseData.token;
                        });
                        
                        // Update logout request body
                        document.querySelector('#logout-data').value = JSON.stringify({
                            token: responseData.token
                        }, null, 4);
                    }
                } catch (e) {
                    console.log('Not a valid JSON response');
                }
            }
        });
    </script>
</body>
</html>
