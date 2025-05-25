<?php
/**
 * Messaging API Endpoints Test Page
 * 
 * This page provides a simple interface to test all messaging endpoints
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
define('BASE_CONVERSATIONS_URL', 'http://localhost/webdev/backend/src/api/conversations/');
define('BASE_MESSAGES_URL', 'http://localhost/webdev/backend/src/api/messages/');
define('ADJUSTED_API_URL', true); // Set to false if original URL should be used

// Function to get the proper API URL
function getApiUrl($endpoint, $pathParams = []) {
    if (ADJUSTED_API_URL) {
        switch ($endpoint) {
            case 'get_conversations':
                return BASE_CONVERSATIONS_URL . 'get_conversations.php';
            case 'create_conversation':
                return BASE_CONVERSATIONS_URL . 'create_conversation.php';
            case 'get_conversation_details':
                $conversationId = !empty($pathParams['conversationId']) ? $pathParams['conversationId'] : '';
                if (!empty($conversationId)) {
                    return BASE_CONVERSATIONS_URL . 'get_conversation_details.php?conversationId=' . $conversationId;
                }
                return BASE_CONVERSATIONS_URL . 'get_conversation_details.php';
            case 'get_conversation_messages':
                $conversationId = !empty($pathParams['conversationId']) ? $pathParams['conversationId'] : '';
                if (!empty($conversationId)) {
                    return BASE_CONVERSATIONS_URL . 'get_conversation_messages.php?conversationId=' . $conversationId;
                }
                return BASE_CONVERSATIONS_URL . 'get_conversation_messages.php';
            case 'send_message':
                $conversationId = !empty($pathParams['conversationId']) ? $pathParams['conversationId'] : '';
                if (!empty($conversationId)) {
                    return BASE_CONVERSATIONS_URL . 'send_message.php?conversationId=' . $conversationId;
                }
                return BASE_CONVERSATIONS_URL . 'send_message.php';
            case 'mark_message_read':
                $messageId = !empty($pathParams['messageId']) ? $pathParams['messageId'] : '';
                if (!empty($messageId)) {
                    return BASE_MESSAGES_URL . 'mark_message_read.php?messageId=' . $messageId;
                }
                return BASE_MESSAGES_URL . 'mark_message_read.php';
            case 'delete_message':
                $messageId = !empty($pathParams['messageId']) ? $pathParams['messageId'] : '';
                if (!empty($messageId)) {
                    return BASE_MESSAGES_URL . 'delete_message.php?messageId=' . $messageId;
                }
                return BASE_MESSAGES_URL . 'delete_message.php';
            default:
                return BASE_CONVERSATIONS_URL . $endpoint . '.php';
        }
    } else {
        // RESTful URLs without .php extension
        switch ($endpoint) {
            case 'get_conversations':
                return 'http://localhost/webdev/backend/src/api/conversations';
            case 'create_conversation':
                return 'http://localhost/webdev/backend/src/api/conversations';
            case 'get_conversation_details':
                $conversationId = !empty($pathParams['conversationId']) ? $pathParams['conversationId'] : '';
                if (!empty($conversationId)) {
                    return 'http://localhost/webdev/backend/src/api/conversations/' . $conversationId;
                }
                return 'http://localhost/webdev/backend/src/api/conversations/';
            case 'get_conversation_messages':
                $conversationId = !empty($pathParams['conversationId']) ? $pathParams['conversationId'] : '';
                if (!empty($conversationId)) {
                    return 'http://localhost/webdev/backend/src/api/conversations/' . $conversationId . '/messages';
                }
                return 'http://localhost/webdev/backend/src/api/conversations/messages';
            case 'send_message':
                $conversationId = !empty($pathParams['conversationId']) ? $pathParams['conversationId'] : '';
                if (!empty($conversationId)) {
                    return 'http://localhost/webdev/backend/src/api/conversations/' . $conversationId . '/messages';
                }
                return 'http://localhost/webdev/backend/src/api/conversations/messages';
            case 'mark_message_read':
                $messageId = !empty($pathParams['messageId']) ? $pathParams['messageId'] : '';
                if (!empty($messageId)) {
                    return 'http://localhost/webdev/backend/src/api/messages/' . $messageId . '/read';
                }
                return 'http://localhost/webdev/backend/src/api/messages/read';
            case 'delete_message':
                $messageId = !empty($pathParams['messageId']) ? $pathParams['messageId'] : '';
                if (!empty($messageId)) {
                    return 'http://localhost/webdev/backend/src/api/messages/' . $messageId;
                }
                return 'http://localhost/webdev/backend/src/api/messages/';
            default:
                return 'http://localhost/webdev/backend/src/api/conversations/' . $endpoint;
        }
    }
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$activeTab = $_GET['tab'] ?? 'get_conversations';
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
    if (in_array($endpoint, ['get_conversation_details', 'get_conversation_messages', 'send_message'])) {
        $pathParams['conversationId'] = isset($data['conversationId']) ? $data['conversationId'] : '';
        unset($data['conversationId']);  // Remove from body data if it exists
    }
    
    if (in_array($endpoint, ['mark_message_read', 'delete_message'])) {
        $pathParams['messageId'] = isset($data['messageId']) ? $data['messageId'] : '';
        unset($data['messageId']);  // Remove from body data if it exists
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
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Add POST data if method is POST/PUT/DELETE
    if (in_array($method, ['POST', 'PUT', 'DELETE']) && !empty($data)) {
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
    
    return $json;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaging API Test Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .response-container {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .response-info {
            margin-bottom: 10px;
        }
        .response-info span {
            margin-right: 10px;
        }
        .pre-scrollable {
            max-height: 400px;
            overflow-y: auto;
        }
        .endpoint-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .token-field {
            font-family: monospace;
            font-size: 12px;
        }
        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Messaging API Test Page</h1>
        <p class="text-muted">Test the messaging endpoints for conversations and messages.</p>
        
        <?= $message ?>
        
        <!-- Global Token Input -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Authentication Token</h5>
                    </div>
                    <div class="card-body">
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
        
        <ul class="nav nav-tabs" id="messagingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'get_conversations' ? 'active' : '' ?>" id="get-conversations-tab" data-bs-toggle="tab" data-bs-target="#get-conversations" type="button" role="tab">Get Conversations</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'create_conversation' ? 'active' : '' ?>" id="create-conversation-tab" data-bs-toggle="tab" data-bs-target="#create-conversation" type="button" role="tab">Create Conversation</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'get_conversation_details' ? 'active' : '' ?>" id="get-conversation-details-tab" data-bs-toggle="tab" data-bs-target="#get-conversation-details" type="button" role="tab">Get Conversation Details</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'get_conversation_messages' ? 'active' : '' ?>" id="get-conversation-messages-tab" data-bs-toggle="tab" data-bs-target="#get-conversation-messages" type="button" role="tab">Get Messages</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'send_message' ? 'active' : '' ?>" id="send-message-tab" data-bs-toggle="tab" data-bs-target="#send-message" type="button" role="tab">Send Message</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'mark_message_read' ? 'active' : '' ?>" id="mark-message-read-tab" data-bs-toggle="tab" data-bs-target="#mark-message-read" type="button" role="tab">Mark Message Read</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'delete_message' ? 'active' : '' ?>" id="delete-message-tab" data-bs-toggle="tab" data-bs-target="#delete-message" type="button" role="tab">Delete Message</button>
            </li>
        </ul>
        
        <div class="tab-content" id="messagingTabContent">
            <!-- Get Conversations Tab -->
            <div class="tab-pane fade <?= $activeTab === 'get_conversations' ? 'show active' : '' ?>" id="get-conversations" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Get User Conversations</h3>
                    <p class="text-muted">Endpoint: GET /api/conversations</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="get_conversations">
                    <input type="hidden" name="method" value="GET">
                    
                    <div class="form-group">
                        <label for="get-conversations-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="get-conversations-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="get-conversations-data">Request Parameters:</label>
                        <textarea class="form-control" id="get-conversations-data" name="data" rows="4">{
    "page": 1,
    "limit": 20
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Create Conversation Tab -->
            <div class="tab-pane fade <?= $activeTab === 'create_conversation' ? 'show active' : '' ?>" id="create-conversation" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Create Conversation</h3>
                    <p class="text-muted">Endpoint: POST /api/conversations</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="create_conversation">
                    <input type="hidden" name="method" value="POST">
                    
                    <div class="form-group">
                        <label for="create-conversation-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="create-conversation-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="create-conversation-data">Request Body:</label>
                        <textarea class="form-control" id="create-conversation-data" name="data" rows="6">{
    "participants": [2, 3],
    "is_group_chat": false,
    "group_name": null
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Get Conversation Details Tab -->
            <div class="tab-pane fade <?= $activeTab === 'get_conversation_details' ? 'show active' : '' ?>" id="get-conversation-details" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Get Conversation Details</h3>
                    <p class="text-muted">Endpoint: GET /api/conversations/:conversationId</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="get_conversation_details">
                    <input type="hidden" name="method" value="GET">
                    
                    <div class="form-group">
                        <label for="get-conversation-details-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="get-conversation-details-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="get-conversation-details-data">Request Parameters:</label>
                        <textarea class="form-control" id="get-conversation-details-data" name="data" rows="3">{
    "conversationId": "1"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Get Conversation Messages Tab -->
            <div class="tab-pane fade <?= $activeTab === 'get_conversation_messages' ? 'show active' : '' ?>" id="get-conversation-messages" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Get Conversation Messages</h3>
                    <p class="text-muted">Endpoint: GET /api/conversations/:conversationId/messages</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="get_conversation_messages">
                    <input type="hidden" name="method" value="GET">
                    
                    <div class="form-group">
                        <label for="get-conversation-messages-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="get-conversation-messages-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="get-conversation-messages-data">Request Parameters:</label>
                        <textarea class="form-control" id="get-conversation-messages-data" name="data" rows="6">{
    "conversationId": "1",
    "page": 1,
    "limit": 50,
    "before_message_id": null
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Send Message Tab -->
            <div class="tab-pane fade <?= $activeTab === 'send_message' ? 'show active' : '' ?>" id="send-message" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Send Message</h3>
                    <p class="text-muted">Endpoint: POST /api/conversations/:conversationId/messages</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="send_message">
                    <input type="hidden" name="method" value="POST">
                    
                    <div class="form-group">
                        <label for="send-message-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="send-message-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="send-message-data">Request Body:</label>
                        <textarea class="form-control" id="send-message-data" name="data" rows="4">{
    "conversationId": "1",
    "content": "Hello, this is a test message!"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Mark Message Read Tab -->
            <div class="tab-pane fade <?= $activeTab === 'mark_message_read' ? 'show active' : '' ?>" id="mark-message-read" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Mark Message as Read</h3>
                    <p class="text-muted">Endpoint: PUT /api/messages/:messageId/read</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="mark_message_read">
                    <input type="hidden" name="method" value="PUT">
                    
                    <div class="form-group">
                        <label for="mark-message-read-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="mark-message-read-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="mark-message-read-data">Request Parameters:</label>
                        <textarea class="form-control" id="mark-message-read-data" name="data" rows="3">{
    "messageId": "1"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
            
            <!-- Delete Message Tab -->
            <div class="tab-pane fade <?= $activeTab === 'delete_message' ? 'show active' : '' ?>" id="delete-message" role="tabpanel">
                <div class="endpoint-header">
                    <h3>Delete Message</h3>
                    <p class="text-muted">Endpoint: DELETE /api/messages/:messageId</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="endpoint" value="delete_message">
                    <input type="hidden" name="method" value="DELETE">
                    
                    <div class="form-group">
                        <label for="delete-message-token">Authentication Token:</label>
                        <textarea class="form-control token-field" id="delete-message-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="delete-message-data">Request Parameters:</label>
                        <textarea class="form-control" id="delete-message-data" name="data" rows="3">{
    "messageId": "1"
}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
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
            
            <?php if ($rawResponse): ?>
            <h5 class="mt-3">Raw Response</h5>
            <pre class="pre-scrollable"><code><?= htmlspecialchars($rawResponse) ?></code></pre>
            <?php endif; ?>
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
            
            <h5>Testing Flow:</h5>
            <ol>
                <li><strong>Create Conversation:</strong> Create a new conversation with one or more participants</li>
                <li><strong>Get Conversations:</strong> Retrieve all conversations for the authenticated user</li>
                <li><strong>Get Conversation Details:</strong> Get detailed information about a specific conversation</li>
                <li><strong>Send Message:</strong> Send a message to a conversation</li>
                <li><strong>Get Messages:</strong> Retrieve messages from a conversation</li>
                <li><strong>Mark Message Read:</strong> Mark a message as read (can only mark others' messages)</li>
                <li><strong>Delete Message:</strong> Delete a message (can only delete your own messages)</li>
            </ol>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
                    const token = decodeURIComponent(hash.split('=')[1]);
                    globalToken.value = token;
                    tokenFields.forEach(field => {
                        field.value = token;
                    });
                    
                    // Save to session
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', window.location.href, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send('save_token=1&token=' + encodeURIComponent(token));
                    
                    // Clear the hash
                    window.location.hash = '';
                }
            }
        });
    </script>
</body>
</html>
