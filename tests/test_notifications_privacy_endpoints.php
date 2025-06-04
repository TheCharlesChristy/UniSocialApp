<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications and Privacy API Test Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea, select, button {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .delete-btn {
            background-color: #dc3545 !important;
        }
        .delete-btn:hover {
            background-color: #c82333 !important;
        }
        .response {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            background: #e9ecef;
            border: 1px solid #ddd;
            border-bottom: none;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
        }
        .tab.active {
            background: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .auth-section {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
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
        .status-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        textarea {
            resize: vertical;
            min-height: 60px;
        }
        small {
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <a href="http://localhost/tests" class="back-to-tests-btn">← Back to Tests</a>
    <h1>Notifications and Privacy API Test Page</h1>

    <!-- Authentication Section -->
    <div class="auth-section">
        <h3>Authentication</h3>
        <div class="form-group">
            <label for="authToken">Access Token:</label>
            <input type="text" id="authToken" placeholder="Enter your access token here">
            <small>Get your token from the login endpoint first</small>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="showTab('notifications')">Notifications</div>
        <div class="tab" onclick="showTab('privacy')">Privacy Settings</div>
    </div>

    <!-- Notifications Tab -->
    <div id="notifications" class="tab-content active">
        <!-- Get Notifications -->
        <div class="container">
            <h3>Get Notifications</h3>
            <div class="form-group">
                <label for="notifPage">Page:</label>
                <input type="number" id="notifPage" value="1" min="1">
            </div>
            <div class="form-group">
                <label for="notifLimit">Limit:</label>
                <input type="number" id="notifLimit" value="10" min="1" max="50">
            </div>
            <div class="form-group">
                <label for="notifFilter">Filter (unread only):</label>
                <select id="notifFilter">
                    <option value="">All notifications</option>
                    <option value="unread">Unread only</option>
                </select>
            </div>
            <button onclick="getNotifications()">Get Notifications</button>
            <div id="notificationsResponse" class="response"></div>
        </div>

        <!-- Get Unread Count -->
        <div class="container">
            <h3>Get Unread Count</h3>
            <button onclick="getUnreadCount()">Get Unread Count</button>
            <div id="unreadCountResponse" class="response"></div>
        </div>

        <!-- Mark Notification as Read -->
        <div class="container">
            <h3>Mark Notification as Read</h3>
            <div class="form-group">
                <label for="markReadId">Notification ID:</label>
                <input type="number" id="markReadId" placeholder="Enter notification ID">
            </div>
            <button onclick="markNotificationRead()">Mark as Read</button>
            <div id="markReadResponse" class="response"></div>
        </div>

        <!-- Mark All as Read -->
        <div class="container">
            <h3>Mark All Notifications as Read</h3>
            <button onclick="markAllRead()">Mark All as Read</button>
            <div id="markAllReadResponse" class="response"></div>
        </div>

        <!-- Create Notification (Admin) -->
        <div class="container">
            <h3>Create Notification (Admin Only)</h3>
            <div class="form-group">
                <label for="createRecipientId">Recipient User ID:</label>
                <input type="number" id="createRecipientId" placeholder="Enter recipient user ID">
            </div>
            <div class="form-group">
                <label for="createType">Notification Type:</label>
                <select id="createType">
                    <option value="like">Like</option>
                    <option value="comment">Comment</option>
                    <option value="friend_request">Friend Request</option>
                    <option value="friend_accept">Friend Accept</option>
                    <option value="mention">Mention</option>
                    <option value="tag">Tag</option>
                </select>
            </div>
            <div class="form-group">
                <label for="createContentType">Related Content Type:</label>
                <select id="createContentType">
                    <option value="post">Post</option>
                    <option value="comment">Comment</option>
                    <option value="user">User</option>
                    <option value="message">Message</option>
                </select>
            </div>
            <div class="form-group">
                <label for="createContentId">Related Content ID:</label>
                <input type="number" id="createContentId" placeholder="Enter related content ID">
            </div>
            <button onclick="createNotification()">Create Notification</button>
            <div id="createNotificationResponse" class="response"></div>
        </div>

        <!-- Delete Notification -->
        <div class="container">
            <h3>Delete Notification</h3>
            <div class="form-group">
                <label for="deleteNotifId">Notification ID:</label>
                <input type="number" id="deleteNotifId" placeholder="Enter notification ID to delete">
            </div>
            <button onclick="deleteNotification()" class="delete-btn">Delete Notification</button>
            <div id="deleteNotificationResponse" class="response"></div>
        </div>
    </div>

    <!-- Privacy Tab -->
    <div id="privacy" class="tab-content">
        <!-- Get Privacy Settings -->
        <div class="container">
            <h3>Get Privacy Settings</h3>
            <button onclick="getPrivacySettings()">Get Privacy Settings</button>
            <div id="privacySettingsResponse" class="response"></div>
        </div>

        <!-- Update Privacy Settings -->
        <div class="container">
            <h3>Update Privacy Settings</h3>
            <div class="form-group">
                <label for="profileVisibility">Profile Visibility:</label>
                <select id="profileVisibility">
                    <option value="public">Public</option>
                    <option value="friends">Friends Only</option>
                    <option value="private">Private</option>
                </select>
            </div>
            <div class="form-group">
                <label for="postsVisibility">Posts Visibility:</label>
                <select id="postsVisibility">
                    <option value="public">Public</option>
                    <option value="friends">Friends Only</option>
                    <option value="private">Private</option>
                </select>
            </div>
            <div class="form-group">
                <label for="friendsVisibility">Friends List Visibility:</label>
                <select id="friendsVisibility">
                    <option value="public">Public</option>
                    <option value="friends">Friends Only</option>
                    <option value="private">Private</option>
                </select>
            </div>
            <div class="form-group">
                <label for="allowFriendRequests">Allow Friend Requests:</label>
                <select id="allowFriendRequests">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label for="showOnlineStatus">Show Online Status:</label>
                <select id="showOnlineStatus">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label for="allowMessagesFrom">Allow Messages From:</label>
                <select id="allowMessagesFrom">
                    <option value="everyone">Everyone</option>
                    <option value="friends">Friends Only</option>
                    <option value="nobody">Nobody</option>
                </select>
            </div>
            <button onclick="updatePrivacySettings()">Update Privacy Settings</button>
            <div id="updatePrivacyResponse" class="response"></div>
        </div>
    </div>

    <script>
        const API_BASE_URL = '../backend/src/api';
        
        // Load saved token on page load
        window.onload = function() {
            const savedToken = localStorage.getItem('authToken');
            if (savedToken) {
                document.getElementById('authToken').value = savedToken;
            }
        };

        // Save token to localStorage whenever it changes
        document.getElementById('authToken').addEventListener('input', function() {
            localStorage.setItem('authToken', this.value);
        });

        // Tab switching
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Helper function to get auth token
        function getAuthToken() {
            return document.getElementById('authToken').value;
        }

        // Helper function to display response
        function displayResponse(elementId, response, isError = false, status = null, responseTime = null) {
            const element = document.getElementById(elementId);
            let displayText = '';
            
            if (status) {
                displayText += `HTTP Status: ${status}\n`;
            }
            if (responseTime) {
                displayText += `Response Time: ${responseTime}ms\n`;
            }
            if (status || responseTime) {
                displayText += '\nResponse Data:\n';
            }
            
            displayText += JSON.stringify(response, null, 2);
            
            element.textContent = displayText;
            element.className = 'response ' + (isError ? 'error' : 'success');
        }

        // Helper function to make API requests
        async function makeRequest(url, method = 'GET', data = null) {
            const token = getAuthToken();
            const startTime = performance.now();
            
            const headers = {
                'Content-Type': 'application/json'
            };

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            const config = {
                method: method,
                headers: headers
            };

            if (data && (method === 'POST' || method === 'PUT' || method === 'DELETE')) {
                config.body = JSON.stringify(data);
            }

            try {
                const response = await fetch(url, config);
                const endTime = performance.now();
                const responseTime = Math.round((endTime - startTime) * 100) / 100;
                
                const result = await response.json();
                return { 
                    success: response.ok, 
                    data: result, 
                    status: response.status,
                    responseTime: responseTime
                };
            } catch (error) {
                const endTime = performance.now();
                const responseTime = Math.round((endTime - startTime) * 100) / 100;
                
                return { 
                    success: false, 
                    error: { message: error.message },
                    status: 'Network Error',
                    responseTime: responseTime
                };
            }
        }

        // Notification API functions
        async function getNotifications() {
            const page = document.getElementById('notifPage').value;
            const limit = document.getElementById('notifLimit').value;
            const filter = document.getElementById('notifFilter').value;

            let url = `${API_BASE_URL}/notifications/get_notifications.php?page=${page}&limit=${limit}`;
            if (filter) {
                url += `&filter=${filter}`;
            }
            
            const result = await makeRequest(url);
            displayResponse('notificationsResponse', result.success ? result.data : result.error, !result.success, result.status, result.responseTime);
        }

        async function getUnreadCount() {
            const result = await makeRequest(`${API_BASE_URL}/notifications/unread_count.php`);
            displayResponse('unreadCountResponse', result.success ? result.data : result.error, !result.success, result.status, result.responseTime);
        }

        async function markNotificationRead() {
            const notificationId = document.getElementById('markReadId').value;
            if (!notificationId) {
                alert('Please enter a notification ID');
                return;
            }

            const result = await makeRequest(`${API_BASE_URL}/notifications/mark_read.php?notificationId=${notificationId}`, 'POST', { notificationId: parseInt(notificationId) });
            displayResponse('markReadResponse', result.success ? result.data : result.error, !result.success, result.status, result.responseTime);
        }

        async function markAllRead() {
            const result = await makeRequest(`${API_BASE_URL}/notifications/mark_all_read.php`, 'POST');
            displayResponse('markAllReadResponse', result.success ? result.data : result.error, !result.success, result.status, result.responseTime);
        }

        async function createNotification() {
            const recipientId = document.getElementById('createRecipientId').value;
            const type = document.getElementById('createType').value;
            const contentType = document.getElementById('createContentType').value;
            const contentId = document.getElementById('createContentId').value;

            if (!recipientId || !contentId) {
                alert('Please fill in recipient ID and content ID');
                return;
            }

            const data = {
                recipient_id: parseInt(recipientId),
                type: type,
                related_content_type: contentType,
                related_content_id: parseInt(contentId)
            };

            const result = await makeRequest(`${API_BASE_URL}/notifications/create_notification.php`, 'POST', data);
            displayResponse('createNotificationResponse', result.success ? result.data : result.error, !result.success, result.status, result.responseTime);
        }

        async function deleteNotification() {
            const notificationId = document.getElementById('deleteNotifId').value;
            if (!notificationId) {
                alert('Please enter a notification ID');
                return;
            }

            if (!confirm('Are you sure you want to delete this notification?')) {
                return;
            }

            const result = await makeRequest(`${API_BASE_URL}/notifications/delete_notification.php?notificationId=${notificationId}`, 'DELETE', { notificationId: parseInt(notificationId) });
            displayResponse('deleteNotificationResponse', result.success ? result.data : result.error, !result.success, result.status, result.responseTime);
        }

        // Privacy API functions
        async function getPrivacySettings() {
            const result = await makeRequest(`${API_BASE_URL}/privacy/get_privacy.php`);
            displayResponse('privacySettingsResponse', result.success ? result.data : result.error, !result.success, result.status, result.responseTime);
        }

        async function updatePrivacySettings() {
            const data = {
                profile_visibility: document.getElementById('profileVisibility').value,
                posts_visibility: document.getElementById('postsVisibility').value,
                friends_visibility: document.getElementById('friendsVisibility').value,
                allow_friend_requests: parseInt(document.getElementById('allowFriendRequests').value),
                show_online_status: parseInt(document.getElementById('showOnlineStatus').value),
                allow_messages_from: document.getElementById('allowMessagesFrom').value
            };

            const result = await makeRequest(`${API_BASE_URL}/privacy/update_privacy.php`, 'PUT', data);
            displayResponse('updatePrivacyResponse', result.success ? result.data : result.error, !result.success, result.status, result.responseTime);
        }
    </script>
</body>
</html>
    }
    
    // Set the active tab based on the endpoint
    $activeTab = $endpoint;
    
    // Make the API request
    $startTime = microtime(true);
    $responseData = makeRequest($endpoint, $data, $headers, $method, $pathParams);
    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    // Store response data
    $response = $responseData['data'];
    $responseCode = $responseData['http_code'];
    $rawResponse = $responseData['raw_response'];
    $requestUrl = $responseData['url'];
}

/**
 * Make HTTP request to API endpoint
 */
function makeRequest($endpoint, $data = [], $headers = [], $method = 'GET', $pathParams = []) {
    $url = getApiUrl($endpoint, $pathParams);
    
    // Add query parameters for GET requests
    if ($method === 'GET' && !empty($data)) {
        $url .= '?' . http_build_query($data);
    }
    
    $ch = curl_init();
    
    // Basic cURL options
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array_merge([
            'Content-Type: application/json',
            'Accept: application/json'
        ], $headers),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    // Add request body for non-GET requests
    if ($method !== 'GET' && !empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'data' => ['error' => 'cURL Error: ' . $error],
            'http_code' => 0,
            'raw_response' => '',
            'url' => $url
        ];
    }
    
    // Try to decode JSON response
    $decodedResponse = json_decode($response, true);
    if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
        $decodedResponse = ['raw_response' => $response, 'json_error' => json_last_error_msg()];
    }
    
    return [
        'data' => $decodedResponse,
        'http_code' => $httpCode,
        'raw_response' => $response,
        'url' => $url
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications & Privacy API Test Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .response-container {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            max-height: 500px;
            overflow-y: auto;
        }
        .status-success { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .endpoint-header {
            background: #e9ecef;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .token-field {
            font-family: monospace;
            font-size: 12px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .nav-tabs .nav-link {
            font-size: 14px;
        }
        .tab-content {
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: none;
            background: white;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            font-size: 12px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .info-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="mt-4 mb-4">Notifications & Privacy API Test Page</h1>
                
                <div class="info-box">
                    <h5>API Endpoints Testing</h5>
                    <p><strong>Base URL:</strong> <?= BASE_API_URL ?></p>
                    <p class="mb-0"><strong>Instructions:</strong> Use a valid authentication token to test the endpoints. The token will be saved across requests.</p>
                </div>

                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <!-- Notification Tabs -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'get_notifications' ? 'active' : '' ?>" id="get-notifications-tab" data-bs-toggle="tab" data-bs-target="#get-notifications" type="button" role="tab">Get Notifications</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'mark_notification_read' ? 'active' : '' ?>" id="mark-read-tab" data-bs-toggle="tab" data-bs-target="#mark-read" type="button" role="tab">Mark Read</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'mark_all_read' ? 'active' : '' ?>" id="mark-all-read-tab" data-bs-toggle="tab" data-bs-target="#mark-all-read" type="button" role="tab">Mark All Read</button>
                    </li>                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'unread_count' ? 'active' : '' ?>" id="unread-count-tab" data-bs-toggle="tab" data-bs-target="#unread-count" type="button" role="tab">Unread Count</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'create_notification' ? 'active' : '' ?>" id="create-notification-tab" data-bs-toggle="tab" data-bs-target="#create-notification" type="button" role="tab">Create Notification</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'delete_notification' ? 'active' : '' ?>" id="delete-notification-tab" data-bs-toggle="tab" data-bs-target="#delete-notification" type="button" role="tab">Delete Notification</button>
                    </li>
                    <!-- Privacy Tabs -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'get_privacy' ? 'active' : '' ?>" id="get-privacy-tab" data-bs-toggle="tab" data-bs-target="#get-privacy" type="button" role="tab">Get Privacy</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'update_privacy' ? 'active' : '' ?>" id="update-privacy-tab" data-bs-toggle="tab" data-bs-target="#update-privacy" type="button" role="tab">Update Privacy</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="myTabContent">
                    
                    <!-- Get Notifications Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'get_notifications' ? 'show active' : '' ?>" id="get-notifications" role="tabpanel">
                        <div class="endpoint-header">
                            <h3>Get Notifications</h3>
                            <p class="text-muted">Endpoint: GET /api/notifications</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="get_notifications">
                            <input type="hidden" name="method" value="GET">
                            
                            <div class="form-group">
                                <label for="get-notifications-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="get-notifications-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="get-notifications-data">Request Parameters:</label>
                                <textarea class="form-control" id="get-notifications-data" name="data" rows="6">{
    "page": "1",
    "limit": "10",
    "filter": "all"
}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>
                    </div>
                    
                    <!-- Mark Notification Read Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'mark_notification_read' ? 'show active' : '' ?>" id="mark-read" role="tabpanel">
                        <div class="endpoint-header">
                            <h3>Mark Notification as Read</h3>
                            <p class="text-muted">Endpoint: PUT /api/notifications/:notificationId/read</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="mark_notification_read">
                            <input type="hidden" name="method" value="PUT">
                            
                            <div class="form-group">
                                <label for="mark-read-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="mark-read-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="mark-read-data">Request Data:</label>
                                <textarea class="form-control" id="mark-read-data" name="data" rows="4">{
    "notificationId": "1"
}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>
                    </div>
                    
                    <!-- Mark All Read Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'mark_all_read' ? 'show active' : '' ?>" id="mark-all-read" role="tabpanel">
                        <div class="endpoint-header">
                            <h3>Mark All Notifications as Read</h3>
                            <p class="text-muted">Endpoint: PUT /api/notifications/read-all</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="mark_all_read">
                            <input type="hidden" name="method" value="PUT">
                            
                            <div class="form-group">
                                <label for="mark-all-read-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="mark-all-read-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="mark-all-read-data">Request Data:</label>
                                <textarea class="form-control" id="mark-all-read-data" name="data" rows="3">{
}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>
                    </div>
                    
                    <!-- Unread Count Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'unread_count' ? 'show active' : '' ?>" id="unread-count" role="tabpanel">
                        <div class="endpoint-header">
                            <h3>Get Unread Notification Count</h3>
                            <p class="text-muted">Endpoint: GET /api/notifications/unread-count</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="unread_count">
                            <input type="hidden" name="method" value="GET">
                            
                            <div class="form-group">
                                <label for="unread-count-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="unread-count-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="unread-count-data">Request Parameters:</label>
                                <textarea class="form-control" id="unread-count-data" name="data" rows="3">{
}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>                    </div>
                    
                    <!-- Create Notification Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'create_notification' ? 'show active' : '' ?>" id="create-notification" role="tabpanel">
                        <div class="endpoint-header">
                            <h3>Create Notification</h3>
                            <p class="text-muted">Endpoint: POST /api/notifications</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="create_notification">
                            <input type="hidden" name="method" value="POST">
                            
                            <div class="form-group">
                                <label for="create-notification-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="create-notification-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="create-notification-data">Request Body:</label>
                                <textarea class="form-control" id="create-notification-data" name="data" rows="8">{
    "recipient_id": "2",
    "type": "like",
    "related_content_type": "post",
    "related_content_id": "1"
}</textarea>
                                <small class="form-text text-muted">Valid types: like, comment, friend_request, friend_accept, mention, tag<br>Valid content types: post, comment, user, message</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>
                    </div>
                    
                    <!-- Delete Notification Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'delete_notification' ? 'show active' : '' ?>" id="delete-notification" role="tabpanel">
                        <div class="endpoint-header">
                            <h3>Delete Notification</h3>
                            <p class="text-muted">Endpoint: DELETE /api/notifications/:notificationId</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="delete_notification">
                            <input type="hidden" name="method" value="DELETE">
                            
                            <div class="form-group">
                                <label for="delete-notification-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="delete-notification-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="delete-notification-data">Request Parameters:</label>
                                <textarea class="form-control" id="delete-notification-data" name="data" rows="4">{
    "notificationId": "1"
}</textarea>
                                <small class="form-text text-muted">The notification ID to delete (must be sender or recipient)</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>
                    </div>
                    
                    <!-- Get Privacy Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'get_privacy' ? 'show active' : '' ?>" id="get-privacy" role="tabpanel">
                        <div class="endpoint-header">
                            <h3>Get Privacy Settings</h3>
                            <p class="text-muted">Endpoint: GET /api/privacy</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="get_privacy">
                            <input type="hidden" name="method" value="GET">
                            
                            <div class="form-group">
                                <label for="get-privacy-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="get-privacy-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="get-privacy-data">Request Parameters:</label>
                                <textarea class="form-control" id="get-privacy-data" name="data" rows="3">{
}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>
                    </div>
                    
                    <!-- Update Privacy Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'update_privacy' ? 'show active' : '' ?>" id="update-privacy" role="tabpanel">
                        <div class="endpoint-header">
                            <h3>Update Privacy Settings</h3>
                            <p class="text-muted">Endpoint: PUT /api/privacy</p>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="endpoint" value="update_privacy">
                            <input type="hidden" name="method" value="PUT">
                            
                            <div class="form-group">
                                <label for="update-privacy-token">Authentication Token:</label>
                                <textarea class="form-control token-field" id="update-privacy-token" name="token" rows="2"><?= htmlspecialchars($_SESSION['global_token'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="update-privacy-data">Request Body:</label>
                                <textarea class="form-control" id="update-privacy-data" name="data" rows="8">{
    "post_default_privacy": "friends",
    "profile_visibility": "public",
    "friend_list_visibility": "friends",
    "who_can_send_requests": "everyone"
}</textarea>
                                <small class="form-text text-muted">
                                    Valid values:<br>
                                    - post_default_privacy, profile_visibility, friend_list_visibility: public, friends, private<br>
                                    - who_can_send_requests: everyone, friends_of_friends, nobody
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>
                    </div>
                    
                </div>

                <!-- Response Display -->
                <?php if ($response !== null): ?>
                <div class="response-container">
                    <h4>API Response</h4>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>HTTP Status:</strong> 
                            <span class="<?= $responseCode >= 200 && $responseCode < 300 ? 'status-success' : 'status-error' ?>">
                                <?= $responseCode ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Response Time:</strong> <?= $responseTime ?>ms
                        </div>
                        <div class="col-md-6">
                            <strong>Request URL:</strong> <code><?= htmlspecialchars($requestUrl) ?></code>
                        </div>
                    </div>
                    
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Request Body:</h5>
                            <pre><?= htmlspecialchars($requestBody) ?></pre>
                        </div>
                        <div class="col-md-6">
                            <h5>Response Data:</h5>
                            <pre><?= htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) ?></pre>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Instructions -->
                <div class="mt-4">
                    <h4>Testing Instructions</h4>
                    <div class="info-box">
                        <h5>Notification Endpoints:</h5>
                        <ol>
                            <li><strong>Get Notifications:</strong> Retrieve user's notifications with pagination and filtering</li>
                            <li><strong>Mark Notification Read:</strong> Mark a specific notification as read (requires notification ID)</li>
                            <li><strong>Mark All Read:</strong> Mark all unread notifications as read</li>
                            <li><strong>Unread Count:</strong> Get count of unread notifications</li>
                        </ol>
                        
                        <h5>Privacy Endpoints:</h5>
                        <ol>
                            <li><strong>Get Privacy Settings:</strong> Retrieve current user's privacy settings</li>
                            <li><strong>Update Privacy Settings:</strong> Update one or more privacy settings</li>
                        </ol>
                        
                        <h5>Authentication:</h5>
                        <p>All endpoints require a valid JWT token. Get one from the auth endpoints first, then paste it in the token field.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-save token on change
        document.querySelectorAll('.token-field').forEach(field => {
            field.addEventListener('blur', function() {
                const token = this.value;
                if (token) {
                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'save_token=1&token=' + encodeURIComponent(token)
                    });
                }
            });
        });

        // Sync token across all fields
        document.querySelectorAll('.token-field').forEach(field => {
            field.addEventListener('input', function() {
                const newValue = this.value;
                document.querySelectorAll('.token-field').forEach(otherField => {
                    if (otherField !== this) {
                        otherField.value = newValue;
                    }
                });
            });
        });
    </script>
</body>
</html>
