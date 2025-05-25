<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends and Blocking API Test Page</title>
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
        .danger-btn {
            background-color: #dc3545 !important;
        }
        .danger-btn:hover {
            background-color: #c82333 !important;
        }
    </style>
</head>
<body>
    <a href="http://localhost/webdev/tests" class="back-to-tests-btn">← Back to Tests</a>
    <h1>Friends and Blocking API Test Page</h1>

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
        <div class="tab active" onclick="showTab('friends')">Friends</div>
        <div class="tab" onclick="showTab('requests')">Friend Requests</div>
        <div class="tab" onclick="showTab('blocking')">Blocking</div>
    </div>

    <!-- Friends Tab -->
    <div id="friends" class="tab-content active">
        <!-- Get Friends -->
        <div class="container">
            <h3>Get Friends List</h3>
            <div class="form-group">
                <label for="friendsPage">Page:</label>
                <input type="number" id="friendsPage" value="1" min="1">
            </div>
            <div class="form-group">
                <label for="friendsLimit">Limit:</label>
                <input type="number" id="friendsLimit" value="20" min="1" max="100">
            </div>
            <button onclick="getFriends()">Get Friends</button>
            <div id="friendsResponse" class="response"></div>
        </div>

        <!-- Remove Friend -->
        <div class="container">
            <h3>Remove Friend</h3>
            <div class="form-group">
                <label for="removeFriendId">Friend User ID:</label>
                <input type="number" id="removeFriendId" value="7" placeholder="Enter user ID to remove">
            </div>
            <button onclick="removeFriend()" class="danger-btn">Remove Friend</button>
            <div id="removeFriendResponse" class="response"></div>
        </div>
    </div>

    <!-- Friend Requests Tab -->
    <div id="requests" class="tab-content">
        <!-- Get Friend Requests -->
        <div class="container">
            <h3>Get Friend Requests</h3>
            <div class="form-group">
                <label for="requestsPage">Page:</label>
                <input type="number" id="requestsPage" value="1" min="1">
            </div>
            <div class="form-group">
                <label for="requestsLimit">Limit:</label>
                <input type="number" id="requestsLimit" value="20" min="1" max="100">
            </div>
            <button onclick="getRequests()">Get Requests</button>
            <div id="requestsResponse" class="response"></div>
        </div>

        <!-- Send Friend Request -->
        <div class="container">
            <h3>Send Friend Request</h3>
            <div class="form-group">
                <label for="sendRequestUserId">Target User ID:</label>
                <input type="number" id="sendRequestUserId" value="7" placeholder="Enter user ID to send request to">
            </div>
            <button onclick="sendRequest()">Send Request</button>
            <div id="sendRequestResponse" class="response"></div>
        </div>

        <!-- Accept Friend Request -->
        <div class="container">
            <h3>Accept Friend Request</h3>
            <div class="form-group">
                <label for="acceptRequestUserId">User ID:</label>
                <input type="number" id="acceptRequestUserId" value="7" placeholder="Enter user ID to accept request from">
            </div>
            <button onclick="acceptRequest()">Accept Request</button>
            <div id="acceptRequestResponse" class="response"></div>
        </div>        <!-- Reject Friend Request -->
        <div class="container">
            <h3>Reject Friend Request</h3>
            <div class="form-group">
                <label for="rejectRequestUserId">User ID:</label>
                <input type="number" id="rejectRequestUserId" value="7" placeholder="Enter user ID to reject request from">
            </div>
            <button onclick="rejectRequest()" class="danger-btn">Reject Request</button>
            <div id="rejectRequestResponse" class="response"></div>
        </div>        <!-- Remove Friend Request -->
        <div class="container">
            <h3>Remove Friend Request (Cancel Sent Request)</h3>
            <div class="form-group">
                <label for="removeRequestUserId">User ID:</label>
                <input type="number" id="removeRequestUserId" value="7" placeholder="Enter user ID to cancel request to">
            </div>
            <button onclick="removeRequest()" class="danger-btn">Remove Request</button>
            <div id="removeRequestResponse" class="response"></div>
        </div>

        <!-- Get Outgoing Requests -->
        <div class="container">
            <h3>Get Outgoing Friend Requests</h3>
            <div class="form-group">
                <label for="outgoingPage">Page:</label>
                <input type="number" id="outgoingPage" value="1" min="1">
            </div>
            <div class="form-group">
                <label for="outgoingLimit">Limit:</label>
                <input type="number" id="outgoingLimit" value="20" min="1" max="100">
            </div>
            <button onclick="getOutgoingRequests()">Get Outgoing Requests</button>
            <div id="outgoingRequestsResponse" class="response"></div>
        </div>
    </div>

    <!-- Blocking Tab -->
    <div id="blocking" class="tab-content">
        <!-- Get Blocked Users -->
        <div class="container">
            <h3>Get Blocked Users</h3>
            <div class="form-group">
                <label for="blockedPage">Page:</label>
                <input type="number" id="blockedPage" value="1" min="1">
            </div>
            <div class="form-group">
                <label for="blockedLimit">Limit:</label>
                <input type="number" id="blockedLimit" value="20" min="1" max="100">
            </div>
            <button onclick="getBlocked()">Get Blocked Users</button>
            <div id="blockedResponse" class="response"></div>
        </div>

        <!-- Block User -->
        <div class="container">
            <h3>Block User</h3>
            <div class="form-group">
                <label for="blockUserId">User ID to Block:</label>
                <input type="number" id="blockUserId" value="7" placeholder="Enter user ID to block">
            </div>
            <button onclick="blockUser()" class="danger-btn">Block User</button>
            <div id="blockResponse" class="response"></div>
        </div>

        <!-- Unblock User -->
        <div class="container">
            <h3>Unblock User</h3>
            <div class="form-group">
                <label for="unblockUserId">User ID to Unblock:</label>
                <input type="number" id="unblockUserId" value="7" placeholder="Enter user ID to unblock">
            </div>
            <button onclick="unblockUser()">Unblock User</button>
            <div id="unblockResponse" class="response"></div>
        </div>
    </div>

    <script>
        const API_BASE_URL = '../backend/src/api';

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
        }        // Helper function to display response
        function displayResponse(elementId, response, isError = false) {
            const element = document.getElementById(elementId);
            
            // Format the response for better display
            let displayData;
            if (isError) {
                // For errors, show both the error and any additional info
                displayData = response;
                if (response.status !== undefined) {
                    displayData = {
                        ...response,
                        http_status: response.status
                    };
                }
            } else {
                displayData = response;
            }
            
            element.textContent = JSON.stringify(displayData, null, 2);
            element.className = 'response ' + (isError ? 'error' : 'success');
        }// Helper function to make API requests
        async function makeRequest(url, method = 'GET', data = null) {
            const token = getAuthToken();
            const headers = {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            };

            const config = {
                method: method,
                headers: headers
            };

            if (data) {
                config.body = JSON.stringify(data);
            }

            try {
                const response = await fetch(url, config);
                let result;
                
                // Try to parse JSON response
                try {
                    result = await response.json();
                } catch (jsonError) {
                    // If JSON parsing fails, get text content
                    const textContent = await response.text();
                    result = { 
                        error: 'Invalid JSON response', 
                        raw_response: textContent,
                        status_code: response.status 
                    };
                }
                
                return { 
                    success: response.ok, 
                    data: response.ok ? result : result, 
                    status: response.status 
                };
            } catch (error) {
                return { 
                    success: false, 
                    error: `Network error: ${error.message}`,
                    status: 0
                };
            }
        }        // Friends API functions
        async function getFriends() {
            const page = document.getElementById('friendsPage').value;
            const limit = document.getElementById('friendsLimit').value;
            
            const url = `${API_BASE_URL}/friends/get_friends.php?page=${page}&limit=${limit}`;
            const result = await makeRequest(url);
            displayResponse('friendsResponse', result.success ? result.data : result, !result.success);
        }        async function removeFriend() {
            const userId = document.getElementById('removeFriendId').value;
            if (!userId) {
                alert('Please enter a user ID');
                return;
            }
            
            if (!confirm('Are you sure you want to remove this friend?')) {
                return;
            }
            
            const url = `${API_BASE_URL}/friends/remove_friend.php/${userId}`;
            const result = await makeRequest(url, 'DELETE');
            displayResponse('removeFriendResponse', result.success ? result.data : result, !result.success);
        }

        // Friend Requests API functions
        async function getRequests() {
            const page = document.getElementById('requestsPage').value;
            const limit = document.getElementById('requestsLimit').value;
            
            const url = `${API_BASE_URL}/friends/get_requests.php?page=${page}&limit=${limit}`;
            const result = await makeRequest(url);
            displayResponse('requestsResponse', result.success ? result.data : result, !result.success);
        }

        async function sendRequest() {
            const userId = document.getElementById('sendRequestUserId').value;
            if (!userId) {
                alert('Please enter a user ID');
                return;
            }
            
            const result = await makeRequest(`${API_BASE_URL}/friends/send_request.php`, 'POST', { user_id: parseInt(userId) });
            displayResponse('sendRequestResponse', result.success ? result.data : result, !result.success);
        }        async function acceptRequest() {
            const userId = document.getElementById('acceptRequestUserId').value;
            if (!userId) {
                alert('Please enter a user ID');
                return;
            }
            
            const url = `${API_BASE_URL}/friends/accept_request.php/${userId}`;
            const result = await makeRequest(url, 'PUT');
            displayResponse('acceptRequestResponse', result.success ? result.data : result, !result.success);
        }        async function rejectRequest() {
            const userId = document.getElementById('rejectRequestUserId').value;
            if (!userId) {
                alert('Please enter a user ID');
                return;
            }
            
            const url = `${API_BASE_URL}/friends/reject_request.php/${userId}`;
            const result = await makeRequest(url, 'PUT');
            displayResponse('rejectRequestResponse', result.success ? result.data : result, !result.success);
        }        async function removeRequest() {
            const userId = document.getElementById('removeRequestUserId').value;
            if (!userId) {
                alert('Please enter a user ID');
                return;
            }
            
            if (!confirm('Are you sure you want to cancel this friend request?')) {
                return;
            }
            
            const url = `${API_BASE_URL}/friends/remove_friend_request.php/${userId}`;
            const result = await makeRequest(url, 'DELETE');
            displayResponse('removeRequestResponse', result.success ? result.data : result, !result.success);
        }

        async function getOutgoingRequests() {
            const page = document.getElementById('outgoingPage').value;
            const limit = document.getElementById('outgoingLimit').value;
            
            const url = `${API_BASE_URL}/friends/get_outgoing_requests.php?page=${page}&limit=${limit}`;
            const result = await makeRequest(url);
            displayResponse('outgoingRequestsResponse', result.success ? result.data : result, !result.success);
        }        // Blocking API functions
        async function getBlocked() {
            const page = document.getElementById('blockedPage').value;
            const limit = document.getElementById('blockedLimit').value;
            
            const url = `${API_BASE_URL}/users/get_blocked.php?page=${page}&limit=${limit}`;
            const result = await makeRequest(url);
            displayResponse('blockedResponse', result.success ? result.data : result, !result.success);
        }

        async function blockUser() {
            const userId = document.getElementById('blockUserId').value;
            if (!userId) {
                alert('Please enter a user ID');
                return;
            }
            
            if (!confirm('Are you sure you want to block this user?')) {
                return;
            }
            
            const url = `${API_BASE_URL}/users/block_user.php?userId=${userId}`;
            const result = await makeRequest(url, 'POST');
            displayResponse('blockResponse', result.success ? result.data : result, !result.success);
        }

        async function unblockUser() {
            const userId = document.getElementById('unblockUserId').value;
            if (!userId) {
                alert('Please enter a user ID');
                return;
            }
            
            const url = `${API_BASE_URL}/users/unblock_user.php?userId=${userId}`;
            const result = await makeRequest(url, 'DELETE');
            displayResponse('unblockResponse', result.success ? result.data : result, !result.success);
        }
    </script>
</body>
</html>
