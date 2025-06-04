<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports API Test Page</title>
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
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
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
        .warning-btn {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }
        .warning-btn:hover {
            background-color: #e0a800 !important;
        }
        .info-box {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .info-box h4 {
            margin-top: 0;
            color: #0056b3;
        }
        .example-values {
            background-color: #f1f3f4;
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 8px;
            margin-top: 5px;
            font-size: 12px;
            color: #5f6368;
        }
    </style>
</head>
<body>
    <a href="http://localhost/webdev/tests" class="back-to-tests-btn">← Back to Tests</a>
    <h1>Reports API Test Page</h1>

    <!-- Authentication Section -->
    <div class="auth-section">
        <h3>Authentication</h3>
        <div class="form-group">
            <label for="authToken">Access Token:</label>
            <input type="text" id="authToken" placeholder="Enter your access token here">
            <small>Get your token from the login endpoint first</small>
        </div>
    </div>

    <!-- Information Box -->
    <div class="info-box">
        <h4>Reports API Testing Information</h4>
        <p><strong>Available Endpoints:</strong></p>
        <ul>
            <li><strong>POST /api/reports</strong> - Create a new report</li>
            <li><strong>GET /api/users/:userId/reports</strong> - Get reports filed by a user</li>
            <li><strong>GET /api/reports/:reportId</strong> - Get specific report by ID (admin only)</li>
        </ul>
        <p><strong>Content Types:</strong> user, post, comment</p>
        <p><strong>Report Statuses:</strong> pending, reviewed, action_taken, dismissed</p>
        <p><strong>Note:</strong> The "Get Report" endpoint requires admin authentication.</p>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="showTab('create-report')">Create Report</div>
        <div class="tab" onclick="showTab('get-reports')">Get User Reports</div>
        <div class="tab" onclick="showTab('get-report')">Get Report (Admin)</div>
    </div>

    <!-- Create Report Tab -->
    <div id="create-report" class="tab-content active">        <div class="container">
            <h3>Create Report</h3>
            <p><strong>Endpoint:</strong> POST /api/reports</p>
            
            <div class="form-group">
                <label for="reportedId">Reported User ID:</label>
                <input type="number" id="reportedId" value="7" placeholder="Enter the ID of the user being reported">
                <div class="example-values">Example: 7 (must be a valid user ID)</div>
            </div>
              <div class="form-group">
                <label for="contentType">Content Type:</label>
                <select id="contentType" onchange="updateContentIdPlaceholder()">
                    <option value="user">User</option>
                    <option value="post">Post</option>
                    <option value="comment">Comment</option>
                </select>
                <div class="example-values">Select the type of content being reported</div>
            </div>
              <div class="form-group">
                <label for="contentId">Content ID:</label>
                <input type="number" id="contentId" value="7" placeholder="Enter the content ID">
                <div class="example-values" id="contentIdHelp">For user reports: must match reported user ID</div>
            </div>
            
            <div class="form-group">
                <label for="reason">Reason:</label>
                <select id="reason">
                    <option value="spam">Spam</option>
                    <option value="harassment">Harassment</option>
                    <option value="inappropriate">Inappropriate Content</option>
                    <option value="violence">Violence</option>
                    <option value="other">Other</option>
                </select>
                <div class="example-values">Select the reason for reporting this content</div>
            </div>
            
            <div class="form-group">
                <label for="description">Description (Optional):</label>
                <textarea id="description" rows="3" placeholder="Additional details about the report...">This content violates community guidelines.</textarea>
                <div class="example-values">Optional: Provide additional context for the report (max 1000 characters)</div>
            </div>
            
            <button onclick="createReport()" class="warning-btn">Submit Report</button>
            <div id="createReportResponse" class="response"></div>
        </div>
    </div>    <!-- Get User Reports Tab -->
    <div id="get-reports" class="tab-content">
        <div class="container">
            <h3>Get User Reports</h3>
            <p><strong>Endpoint:</strong> GET /api/users/:userId/reports</p>
            
            <div class="form-group">
                <label for="getUserId">User ID:</label>
                <input type="number" id="getUserId" value="6" placeholder="Enter user ID to get reports for">
                <div class="example-values">Note: You can only view your own reports. Use your authenticated user ID.</div>
            </div>
            
            <div class="form-group">
                <label for="reportsPage">Page:</label>
                <input type="number" id="reportsPage" value="1" min="1" placeholder="Page number">
                <div class="example-values">Page number for pagination (default: 1)</div>
            </div>
            
            <div class="form-group">
                <label for="reportsLimit">Limit:</label>
                <input type="number" id="reportsLimit" value="20" min="1" max="50" placeholder="Number of reports per page">
                <div class="example-values">Number of reports per page (1-50, default: 20)</div>
            </div>
            
            <h4>Filters (Optional)</h4>
            
            <div class="form-group">
                <label for="filterContentType">Content Type:</label>
                <select id="filterContentType">
                    <option value="">All Content Types</option>
                    <option value="user">User</option>
                    <option value="post">Post</option>
                    <option value="comment">Comment</option>
                </select>
                <div class="example-values">Filter by type of content reported</div>
            </div>
            
            <div class="form-group">
                <label for="filterReportedUserId">Reported User ID:</label>
                <input type="number" id="filterReportedUserId" placeholder="Enter user ID of reported user">
                <div class="example-values">Filter by the ID of the user who was reported</div>
            </div>
            
            <div class="form-group">
                <label for="filterReporterId">Reporter ID:</label>
                <input type="number" id="filterReporterId" placeholder="Enter reporter user ID">
                <div class="example-values">Filter by the ID of the user who made the report (usually same as authenticated user)</div>
            </div>
            
            <div class="form-group">
                <label for="filterReason">Reason:</label>
                <select id="filterReason">
                    <option value="">All Reasons</option>
                    <option value="spam">Spam</option>
                    <option value="harassment">Harassment</option>
                    <option value="inappropriate">Inappropriate Content</option>
                    <option value="violence">Violence</option>
                    <option value="other">Other</option>
                </select>
                <div class="example-values">Filter by report reason</div>
            </div>
            
            <div class="form-group">
                <label for="filterStatus">Status:</label>
                <select id="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="action_taken">Action Taken</option>
                    <option value="dismissed">Dismissed</option>
                </select>
                <div class="example-values">Filter by report status</div>
            </div>
            
            <button onclick="getUserReports()">Get Reports</button>
            <button onclick="clearReportsFilters()" style="background-color: #6c757d; margin-left: 10px;">Clear Filters</button>
            <div id="getUserReportsResponse" class="response"></div>
        </div>
    </div>    <!-- Get Report (Admin) Tab -->
    <div id="get-report" class="tab-content">
        <div class="container">
            <h3>Get Report (Admin Only)</h3>
            <p><strong>Endpoint:</strong> GET /api/reports/:reportId</p>
            
            <div class="info-box">
                <h4>Admin Access Required</h4>
                <p>This endpoint requires admin authentication. Only admin users can retrieve specific report details.</p>
                <p><strong>Features:</strong></p>
                <ul>
                    <li>Detailed report information including all user data</li>
                    <li>Content details based on content type (post, comment, user)</li>
                    <li>Review history and admin notes</li>
                    <li>Reporter and reported user information</li>
                </ul>
            </div>
            
            <div class="form-group">
                <label for="getReportId">Report ID:</label>
                <input type="number" id="getReportId" value="1" placeholder="Enter report ID to retrieve">
                <div class="example-values">Enter the ID of the report you want to retrieve (must exist in database)</div>
            </div>
            
            <button onclick="getReport()">Get Report Details</button>
            <div id="getReportResponse" class="response"></div>
            
            <div class="info-box" style="margin-top: 20px;">
                <h4>Expected Response Structure</h4>
                <p><strong>Success (200):</strong> Detailed report object with reporter, reported user, content details, and review information</p>
                <p><strong>Error (403):</strong> Admin access required</p>
                <p><strong>Error (404):</strong> Report not found</p>
                <p><strong>Error (400):</strong> Invalid report ID</p>
            </div>
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
        }

        // Helper function to display response
        function displayResponse(elementId, data, isError = false) {
            const element = document.getElementById(elementId);
            element.textContent = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
            element.className = 'response ' + (isError ? 'error' : 'success');
        }        // Update content ID placeholder based on content type
        function updateContentIdPlaceholder() {
            const contentType = document.getElementById('contentType').value;
            const contentIdInput = document.getElementById('contentId');
            const helpText = document.getElementById('contentIdHelp');
            
            switch (contentType) {
                case 'user':
                    contentIdInput.placeholder = 'Enter user ID (must match reported user ID)';
                    helpText.textContent = 'For user reports: must match reported user ID';
                    break;
                case 'post':
                    contentIdInput.placeholder = 'Enter post ID';
                    helpText.textContent = 'For post reports: ID of the specific post being reported';
                    break;
                case 'comment':
                    contentIdInput.placeholder = 'Enter comment ID';
                    helpText.textContent = 'For comment reports: ID of the specific comment being reported';
                    break;
            }
        }

        // Clear reports filters
        function clearReportsFilters() {
            document.getElementById('filterContentType').value = '';
            document.getElementById('filterReportedUserId').value = '';
            document.getElementById('filterReporterId').value = '';
            document.getElementById('filterReason').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('reportsPage').value = '1';
        }

        // Helper function to make API requests
        async function makeRequest(url, method = 'GET', body = null) {
            const token = getAuthToken();

            try {
                const config = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    }
                };

                if (body && (method === 'POST' || method === 'PUT')) {
                    config.body = JSON.stringify(body);
                }

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
        }        // Create Report function
        async function createReport() {
            const reportedId = document.getElementById('reportedId').value;
            const contentType = document.getElementById('contentType').value;
            const contentId = document.getElementById('contentId').value;
            const reason = document.getElementById('reason').value;
            const description = document.getElementById('description').value;

            const requestBody = {
                reported_id: parseInt(reportedId),
                content_type: contentType,
                content_id: parseInt(contentId),
                reason: reason
            };

            // Add description if provided
            if (description.trim()) {
                requestBody.description = description.trim();
            }

            const url = `${API_BASE_URL}/reports/create_report.php`;
            const result = await makeRequest(url, 'POST', requestBody);
            displayResponse('createReportResponse', result.success ? result.data : result, !result.success);
        }// Get User Reports function
        async function getUserReports() {
            const userId = document.getElementById('getUserId').value;
            const page = document.getElementById('reportsPage').value;
            const limit = document.getElementById('reportsLimit').value;
            
            // Get filter values
            const contentType = document.getElementById('filterContentType').value;
            const reportedUserId = document.getElementById('filterReportedUserId').value;
            const reporterId = document.getElementById('filterReporterId').value;
            const reason = document.getElementById('filterReason').value;
            const status = document.getElementById('filterStatus').value;

            // Build query parameters
            let queryParams = new URLSearchParams({
                userId: userId,
                page: page,
                limit: limit
            });

            // Add filter parameters if they have values
            if (contentType) queryParams.append('content_type', contentType);
            if (reportedUserId) queryParams.append('reported_user_id', reportedUserId);
            if (reporterId) queryParams.append('reporter_id', reporterId);
            if (reason) queryParams.append('reason', reason);
            if (status) queryParams.append('status', status);

            const url = `${API_BASE_URL}/users/get_user_reports.php?${queryParams.toString()}`;
            const result = await makeRequest(url);
            displayResponse('getUserReportsResponse', result.success ? result.data : result, !result.success);
        }        // Get Report (Admin) function
        async function getReport() {
            const reportId = document.getElementById('getReportId').value;
            
            if (!reportId || reportId <= 0) {
                displayResponse('getReportResponse', 'Please enter a valid report ID', true);
                return;
            }

            const url = `${API_BASE_URL}/reports/get_report.php?id=${reportId}`;
            const result = await makeRequest(url);
            displayResponse('getReportResponse', result.success ? result.data : result, !result.success);
        }

        // Initialize content ID placeholder on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateContentIdPlaceholder();
        });
    </script>
</body>
</html>
